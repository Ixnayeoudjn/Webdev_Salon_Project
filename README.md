# 💇‍♀️ CopyCut Salon Appointment Booking System (Laravel)

A feature-rich and role-based appointment booking system built with Laravel, tailored for salons and service-oriented businesses. This system includes user authentication, role management, service scheduling, and seedable demo accounts for quick testing.

---

## ✨ Key Features

✅ Role-Based Access (Super-admin, Staff, Customer)  
✅ Staff account preloaded with credentials  
✅ Database seeding for roles, users, and services  
✅ Laravel-powered backend and Vue/Blade frontend    

---

## ⚙️ Installation & Setup

Follow these steps to set up the project locally:

### 1. Clone the Repository

git clone https://github.com/Ixnayeoudjn/Webdev_Salon_Project.git  
cd Webdev_Salon_Project

### 2. Create an Empty Database

- Create a new MySQL database (e.g., salon_db)
- Note down the name for your .env setup

### 3. Install Dependencies

composer install  
cp .env.example .env

- Edit `.env` file and set your DB credentials:
  DB_DATABASE=your_database_name  
  DB_USERNAME=your_db_username  
  DB_PASSWORD=your_db_password  

### 4. Set Up the Laravel App

php artisan key:generate  
php artisan migrate  
php artisan db:seed  
php artisan db:seed --class=RoleSeeder  
php artisan db:seed --class=ServiceSeeder  
php artisan db:seed --class=UserSeeder  
php artisan db:seed --class=AssignRoleSeeder

### 5. Compile Frontend Assets

npm install  
npm run dev

> 💡 Open a new terminal tab or window after this step

### 6. Serve the Application

php artisan serve

---

## 🌐 Accessing the System

Visit: http://127.0.0.1:8000

### Admin Login:
Email: admin@gmail.com  
Password: IamAdmin123

### Staff Logins:
Use any of the following emails with password `password`:

- juan-manalo@salon.com  
- sofia-reyes@salon.com  
- althea-dela-cruz@salon.com  
- samantha-mendoza@salon.com  
- anne-santos@salon.com  
- julia-ramos@salon.com  

### Customer Access:
Register a new account through the main site to log in as a customer.

---

## 📦 System Requirements

- PHP 8.1+  
- MySQL/MariaDB  
- Node.js & NPM  
- Composer  
- Laravel 10+

---

## 💬 Support & Contribution

Have suggestions or found bugs? Feel free to open issues or pull requests.

### Ways to Support:
⭐ Star this repo  
🐛 Report bugs  
🤝 Contribute enhancements  

---

## 🔒 License

This project is open-source and free to use under the MIT License.
