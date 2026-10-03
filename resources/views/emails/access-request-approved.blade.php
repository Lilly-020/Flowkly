<x-mail::message>
# Acesso aprovado

Olá, {{ $name }}!

Sua solicitação de acesso ao Flowkly foi aprovada. Use as credenciais abaixo para
entrar no portal:

**E-mail:** {{ $email }}
**Senha temporária:** {{ $password }}

<x-mail::button :url="$loginUrl">
Acessar o Flowkly
</x-mail::button>

Por segurança, recomendamos alterar essa senha assim que acessar sua conta, em
Configurações.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
