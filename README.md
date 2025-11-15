# 🎮 SteamGuessr

test

## 📖 Table of Contents
- [Prerequisites](#-prerequisites)
- [Quick Start](#-quick-start)
- [Tech Stack](#️-tech-stack)
- [User Stories](#-user-stories)
- [UML Diagrams](#-uml-diagrams)
- [Production Build](#-production-build)

---

<details>
<summary>📋 Prerequisites</summary>

Before you begin, ensure you have the following installed:
- **PHP** >= 8.1
- **Composer** - [Download here](https://getcomposer.org/)
- **Node.js** >= 18.x & npm - [Download here](https://nodejs.org/)
- **MySQL** or other database system
- **Steam API Key** - [Get one here](https://steamcommunity.com/dev/apikey)

</details>

<details open>
<summary>⚡ Quick Start</summary>

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/Snevver/SteamGuessr.git
cd SteamGuessr
```

### 2️⃣ Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3️⃣ Environment Setup
```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4️⃣ Database Configuration

Edit your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=steamguessr
DB_USERNAME=root
DB_PASSWORD=your_password

# Add your Steam API key
STEAM_API_KEY=your_steam_api_key_here
```

💡 **Tip:** Create a database named `steamguessr` in your MySQL server before running migrations.

### 5️⃣ Run Migrations
```bash
php artisan migrate
```

### 6️⃣ (Optional) Seed Database
```bash
php artisan db:seed
```

### 7️⃣ Start Development Servers

You need to run **two terminal windows** simultaneously:

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```
This will start the server at `http://127.0.0.1:8000/`

**Terminal 2 - Vite Dev Server (React):**
```bash
npm run dev
```
This compiles your React components in real-time.

### 8️⃣ Open Your Browser

Navigate to: **http://127.0.0.1:8000/**

</details>

<details>
<summary>🛠️ Tech Stack</summary>

- **Backend:** Laravel 10.x
- **Frontend:** React 18
- **Database:** MySQL
- **Bridge:** Inertia.js
- **Styling:** Tailwind CSS
- **Build Tool:** Vite
- **API:** Steam Web API

</details>

<details>
<summary>📝 Summary</summary>

</details>

<details>
<summary>📖 User Stories</summary>

</details>

<details>
<summary>📊 UML Diagrams</summary>

</details>

<details>
<summary>📦 Production Build</summary>

To build for production:
```bash
# Build frontend assets
npm run build
```

</details>

## 📝 License

This project is licensed under the MIT License.

## 👤 Authors

**Sven Hoeksema**
- GitHub: [@Sven](https://github.com/Snevver)

**Son van der Burg**
- GitHub: [@Son](https://github.com/Penguin-09)