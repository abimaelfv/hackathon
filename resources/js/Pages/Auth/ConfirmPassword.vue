<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    password: "",
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Secure Area" />

    <GuestLayout>
        <div class="text-center mb-5">
            <h1 class="block text-xl font-bold text-udh_3">
                Confirmar contraseña
            </h1>
        </div>

        <div class="mb-4 text-sm text-gray-600">
            Esta es una zona segura de la aplicación. Confirme su contraseña
            antes de continuar.
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" :value="$t('Password')" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex justify-end mt-6">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ $t("Confirm") }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
