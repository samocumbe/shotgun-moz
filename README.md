# 🎯 Shotgun MOZ

Sistema de gestão de eventos e bilhética para Moçambique com suporte offline.

![Laravel](https://img.shields.io/badge/Laravel-11.x-red)
![PHP](https://img.shields.io/badge/PHP-8.5-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)

## 🚀 Sobre o Projeto

**Shotgun MOZ** é uma plataforma completa para organizadores de eventos em Moçambique que permite:

- ✅ Criar e gerenciar eventos
- ✅ Vender ingressos online e presencial
- ✅ Validar ingressos via QR Code (offline)
- ✅ Sincronização de dados offline
- ✅ Integração com M-Pesa e e-Mola
- ✅ Dashboard administrativo
- ✅ Gestão de promotores

## 🏗️ Arquitetura

┌─────────────────────────────────────────────────────────┐
│ Frontend Web │
│ (Blade + Bootstrap + JS) │
└─────────────────────┬───────────────────────────────────┘
│
┌─────────────────────▼───────────────────────────────────┐
│ API Laravel │
│ (REST + Sanctum) │
└─────────────────────┬───────────────────────────────────┘
│
┌─────────────────────▼───────────────────────────────────┐
│ MySQL │
└─────────────────────────────────────────────────────────┘


## 📋 Funcionalidades

### Fase 1: Núcleo (Concluído)
- ✅ Autenticação de usuários
- ✅ CRUD de Locais
- ✅ CRUD de Eventos
- ✅ CRUD de Categorias de Ingressos
- ✅ Geração de QR Codes

### Fase 2: Vendas (Em desenvolvimento)
- ⏳ Sistema de pedidos
- ⏳ Integração M-Pesa
- ⏳ Vendas presenciais
- ⏳ Controle de pagamentos

### Fase 3: Scanner Android (Em desenvolvimento)
- ⏳ App Android com scanner QR
- ⏳ Banco SQLite
- ⏳ Sincronização offline

## 📦 Pré-requisitos

- PHP 8.5+
- MySQL 8.0+
- Node.js 20+
- Composer
- XAMPP ou WAMP

## 🔧 Instalação

### 1. Clonar o repositório
```bash
git clone https://github.com/seu-usuario/shotgun-moz.git
cd shotgun-moz

Instalar dependências PHP
composer install

Instalar dependências Node
npm install
npm run build

Configurar ambiente
cp .env.example .env
php artisan key:generate

Configurar banco de dados
Edite o arquivo .env com as credenciais do MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shotgun_moz
DB_USERNAME=root
DB_PASSWORD=

Rodar migrations e seeders
php artisan migrate
php artisan db:seed

Iniciar servidor
php artisan serve

📚 Estrutura do Banco
shotgun_moz
├── users                 (Autenticação)
├── locais                (Locais de eventos)
├── eventos               (Eventos)
├── categorias_ingressos  (Categorias de ingressos)
├── ingressos             (Ingressos individuais)
├── participantes         (Compradores)
├── checkins              (Validações de entrada)
├── dispositivos          (Scanners/POS)
├── sync_queues           (Fila de sincronização)
└── migrations            (Controle)

🗺️ Rotas Principais
Web
Rota	Método	Descrição
/	GET	Página inicial
/admin/dashboard	GET	Dashboard administrativo
/admin/eventos	GET/CRUD	Gestão de eventos
/admin/ingressos	GET/CRUD	Gestão de ingressos

API
Rota	Método	Descrição
/api/v1/eventos	GET	Lista eventos
/api/v1/eventos/{id}/ingressos/vender	POST	Vender ingresso
/api/v1/eventos/{id}/ingressos/validar	POST	Validar QR Code
/api/v1/eventos/{id}/checkins/sincronizar	POST	Sincronizar offline
📱 App Android
O aplicativo Android (em desenvolvimento) permite:

✅ Scanner de QR Code

✅ Venda presencial

✅ Trabalho offline

✅ Sincronização automática

🤝 Contribuição
Fork o projeto

Crie sua branch (git checkout -b feature/AmazingFeature)

Commit suas mudanças (git commit -m 'Add some AmazingFeature')

Push para a branch (git push origin feature/AmazingFeature)

Abra um Pull Request

📄 Licença
Este projeto está sob a licença MIT.

🏆 Créditos
Laravel Framework

Bootstrap 5

Chart.js

DataTables

Desenvolvido com ❤️ para Moçambique

text

---

## 📋 Resumo do que criamos:

| Passo | Conteúdo | Status |
|-------|----------|--------|
| **1** | Rotas (web.php + api.php) | ✅ Concluído |
| **2** | Views (Layout Admin + Dashboard) | ✅ Concluído |
| **3** | Seeders (Locais, Eventos, Categorias) | ✅ Concluído |
| **4** | README do Projeto | ✅ Concluído |

---

## 🚀 Próximos Passos

1. **Criar os Controllers API** para sincronização offline
2. **Criar o RelatórioController** para PDF/Excel
3. **Configurar o Laravel Sanctum** para autenticação API
4. **Desenvolver o App Android**

---

**Quer que eu continue com os Controllers API ou outro aspecto do projeto?** 🚀
