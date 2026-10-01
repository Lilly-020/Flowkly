<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post('/login')
}
</script>

<template>
    <div>
        <h1>Login</h1>

        <form @submit.prevent="submit">
            <div>
                <label for="email">
                    E-mail
                </label>

                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                >

                <span v-if="form.errors.email">
                    {{ form.errors.email }}
                </span>
            </div>

            <div>
                <label for="password">
                    Senha
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                >

                <span v-if="form.errors.password">
                    {{ form.errors.password }}
                </span>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Entrando...' : 'Entrar' }}
            </button>
        </form>

        <a href="/solicitar-acesso">
            Solicitar acesso
        </a>
    </div>
</template>