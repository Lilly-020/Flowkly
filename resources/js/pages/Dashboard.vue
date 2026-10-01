<script setup>
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

const logout = () => {
    router.post('/logout')
}
</script>

<template>
    <div>
        <h1>Dashboard</h1>

        <p>Bem-vindo, {{ page.props.auth.user.name }}</p>

        <form @submit.prevent="logout">
            <button type="submit">
                Sair
            </button>
        </form>
    </div>
</template>
