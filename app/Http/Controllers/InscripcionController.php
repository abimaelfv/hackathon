<?php

namespace App\Http\Controllers;

use App\Models\Inscripciones;
use App\Models\Integrantes;
use App\Models\Personas;
use App\Models\User;
use App\Rules\MiembroUnicoEquipo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InscripcionController extends Controller
{
    public function incripcion()
    {
        $this->authorize('inscripcion');
        $fechaLimite = Carbon::parse(env('FECHA_LIMITE'));

        $ins_id = Integrantes::where('user_id', Auth::id())->value('ins_id');

        $confirmado = Inscripciones::where('ins_estado', 1)->find($ins_id);

        if (!$fechaLimite->isFuture() && empty($confirmado)) {
            $fuera = true;
            return Inertia::render('Inscripcion', compact('fuera'));
        }

        $ins_id ??= $this->IDorCreateInscripcion();

        $inscripcion = Inscripciones::with(['integrantes.user'])
            ->find($ins_id);

        return Inertia::render('Inscripcion', compact('inscripcion'));
    }

    public function inscripciones()
    {
        $this->authorize('inscripciones');
        $inscripciones = Inscripciones::where('ins_estado', 1)
            ->orderBy('ins_fecha')->get();
        return Inertia::render('Inscripciones/Index', compact('inscripciones'));
    }

    public function addMiembro(Request $request): RedirectResponse
    {
        $request->validate([
            'ins_id' => ['required'],
            'nombres' => ['required'],
            'codigo' => ['required', new MiembroUnicoEquipo()],
        ], [
            'nombres.required' => 'Primero debes buscar al estudiante.',
        ]);

        $user = User::where('codigo', $request->codigo)->where('estado', 1)->first();
        if (empty($user)) {
            $persona = Personas::where('per_codigo', $request->codigo)->where('per_estado', 1)->first();
            $user = User::where('email', $request->codigo . '@udh.edu.pe')->first();
            if (empty($user)) {
                $user = new User();
                $user->password = bcrypt($persona->per_codigo);
            }
            $user->codigo = $persona->per_codigo;
            $user->documento = $persona->per_documento;
            $user->name = $persona->per_nombres;
            $user->apellidos = $persona->per_apellidos;
            $user->email = $persona->per_codigo . '@udh.edu.pe';
            $user->genero = $persona->per_genero;
            $user->email_verified_at = time();
            $user->save();
            $user->assignRole('ESTUDIANTE');
        }
        $miembro_id = $user->id;
        $ins_id = $request->ins_id;

        $this->crearIntegrante($ins_id, $miembro_id);

        return redirect()->back()->with('toast', 'saved');
    }

    protected function crearIntegrante($ins_id, $miembro_id)
    {
        DB::beginTransaction();
        try {
            $integrante = Integrantes::firstOrCreate(
                [
                    'user_id' => $miembro_id,
                    'ins_id' => $ins_id,
                ]
            );
            // Si creo su inscripcion lo borro
            $incripcion = Inscripciones::where('user_id', $miembro_id)->first();
            if ($incripcion && $incripcion->ins_id != $ins_id) {
                $incripcion->integrantes()->delete();
                $incripcion->delete();
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function deleteMiembro($id): RedirectResponse
    {
        $integrante = Integrantes::find($id);
        $integrante->delete();
        return redirect()->back()->with('toast', 'deleted');
    }

    public function IDorCreateInscripcion()
    {
        $user_id = Auth::user()->id;
        $inscripcion = Inscripciones::where('user_id', $user_id)->where('ins_estado', '!=', 0)->first();
        if (empty($inscripcion)) {
            $inscripcion = new Inscripciones();
            $inscripcion->user_id = $user_id;
            $inscripcion->save();
        }
        return $inscripcion->ins_id;
    }

    public function upInscripcion(Request $request)
    {
        $request->validate([
            'ins_id' => ['required'],
            'equipo' => ['nullable', "regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ&'+& ]+$/"],
            'categoria' => ['nullable'],
        ], [
            'equipo.regex' => 'El nombre del equipo solo puede contener letras, números, espacios, tildes.'
        ]);

        $ins_id = $request->ins_id;
        $inscripcion = Inscripciones::find($ins_id);
        $inscripcion->ins_equipo = $request->equipo;
        $inscripcion->ins_categoria = $request->categoria;
        $inscripcion->save();

        return redirect()->back()->with('toast', 'saved');
    }

    public function validar($ins_id)
    {
        try {
            if (isset($ins_id)) {
                $id = trim($ins_id);
                $inscripcion = Inscripciones::find($id);
                if (!$inscripcion) {
                    throw new \Exception('Incripcion no encontrado.');
                }
                $existLider = Integrantes::where('user_id', $inscripcion->user_id)->exists();

                $equipo = empty($inscripcion->ins_equipo) ? false : true;
                $categoria = empty($inscripcion->ins_categoria) ? false : true;

                $participantes = Integrantes::where('ins_id', $id)->get();
                $response = [
                    'status' => true,
                    'equipo' => $equipo,
                    'categoria' => $categoria,
                    'lider' => $existLider,
                    'integrantes' => count($participantes)
                ];
            } else {
                throw new \Exception('Datos requeridos incompletos.');
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'msg' => $e->getMessage()];
            return response()->json($response);
        }
    }

    public function confirmar($id): RedirectResponse
    {
        $inscripcion = Inscripciones::find($id);
        $inscripcion->ins_estado = 1;
        $inscripcion->ins_fecha = now();
        $inscripcion->save();
        return redirect()->back();
    }
}
