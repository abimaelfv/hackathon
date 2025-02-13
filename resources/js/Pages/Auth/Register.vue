<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head :title="$t('Register')" />

    <GuestLayout>
        <div class="text-center mb-6">
            <h1 class="block text-2xl font-bold text-udh_3">
                {{ $t("Register") }}
            </h1>
            <p class="mt-3 text-sm text-gray-600">
                ¿Ya tienes una cuenta?
                <Link
                    :href="route('login')"
                    class="text-udh_1 hover:underline decoration-2 font-semibold"
                >
                    Inicia sesión aquí
                </Link>
            </p>
        </div>

        <form @submit.prevent="submit">

            <div class="mt-4">
                <InputLabel for="email" :value="$t('Email')" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="$t('Password')" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    :value="$t('Confirm Password')"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div
                v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                class="mt-4"
            >
                <label for="terms">
                    <div class="flex items-center">
                        <Checkbox
                            id="terms"
                            v-model:checked="form.terms"
                            name="terms"
                            required
                        />

                        <span class="ml-3 text-sm text-gray-600">
                            He leído y acepto las
                            <a
                                href="/pdf/Hackathon2024-Bases.pdf"
                                target="_blank"
                                class="hover:underline hover:text-udh_1 decoration-2 font-semibold"
                            >
                                Bases del hackathon UDH
                            </a>.
                        </span>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </label>
            </div>

            <PrimaryButton
                class="w-full mt-6 py-3 justify-center"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                {{ $t("Register") }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
