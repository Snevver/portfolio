## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- **PHP** >= 8.1
- **Composer** - [Download here](https://getcomposer.org/)
- **Node.js** >= 18.x & npm - [Download here](https://nodejs.org/)
- **MySQL** or other database system

## ⚡ Quick Start

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/Snevver/portfolio.git
cd portfolio
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
DB_DATABASE=portfolio
DB_USERNAME=root
DB_PASSWORD=your_password
```

💡 **Tip:** Create a database named `portfolio` in your MySQL server before running migrations.

### 5️⃣ Run Migrations

```bash
php artisan migrate
```

### 6️⃣ Start Development Servers

You need to run **two terminal windows** simultaneously:

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```
This will start the server at `http://localhost:8000`

**Terminal 2 - Vite Dev Server (React):**
```bash
npm run dev
```
This compiles your React components in real-time.

### 7️⃣ Open Your Browser

Navigate to: **http://localhost:8000**

---

## 🛠️ Tech Stack

- **Backend:** Laravel 10.x
- **Frontend:** React 18
- **Database:** MySQL
- **Bridge:** Inertia.js
- **Styling:** Tailwind CSS
- **Build Tool:** Vite

## 📦 Production Build

To build for production:

```bash
npm run build
```