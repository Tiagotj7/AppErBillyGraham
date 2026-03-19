# Sistema de Controle de Frequência (PHP + MySQL)

Aplicação web para **cadastro de pessoas** e **registro de frequência por data**, com **login**, **perfis de acesso** e **auditoria de alterações** (somente admin). Projetado para rodar em hospedagem compartilhada (ex.: **InfinityFree**) e também pode ser disponibilizado como **app Android** via WebView (PWA/Wrapper).

---
 
## Principais recursos

- **Login e cadastro de usuários** (nome, email e senha)
- **Troca de senha** em **Minha Conta**
- **CRUD de pessoas** (nome, nascimento e posto)
- **Registro de frequência por data** (Presente/Ausente)
- **Histórico de frequência** por pessoa
- **Auditoria (Admin)**:
  - última alteração por data
  - histórico detalhado de mudanças (quem alterou, o que alterou, data/hora)

---

## Perfis de acesso

- **Admin**
  - gerencia pessoas (editar/excluir)
  - visualiza auditoria de alterações
- **Conselheiro**
  - registra frequência e consulta informações
  - não visualiza auditoria

---

## Tecnologias

- PHP (sem framework)
- MySQL (PDO + prepared statements)
- HTML/CSS/JavaScript
- Compatível com InfinityFree / phpMyAdmin

---

## Segurança (resumo)

- Senhas com `password_hash()` / `password_verify()`
- Sessão com cookies seguros (`HttpOnly`, `SameSite`, `Secure` quando HTTPS)
- Proteção CSRF em formulários POST
- SQL com prepared statements (PDO)

---

## Android (como app)

O sistema pode ser publicado como aplicativo Android de duas formas:

1. **PWA (Progressive Web App)**: o usuário “instala” pelo navegador (recomendado quando possível).
2. **Wrapper WebView**: app nativo simples que carrega a URL do sistema.

> Recomendado usar HTTPS no domínio para melhor segurança e compatibilidade.

---
