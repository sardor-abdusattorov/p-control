# Features
- Role Based Access Control
- Responsive Design
- Modal Form
- Bulk Action
- Light/Dark Mode
- Toast Notification
- Rich Feature Datatable Serverside
- Tooltip
- Localization (EN/RU/UZ)
- SSR (Server Side Rendering)
# Requirements
- Php 8.2
- Composer
- Mysql
- Apache
# Installation
``` bash
clone this repository
cd project
composer update
npm install
cp .env.example .env
php artisan key:generate

SETTING UP DB CONNECTION IN .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brive
DB_USERNAME=root
DB_PASSWORD=

php artisan migrate:fresh --seed

START THE SERVER
npm run dev / npm run build
php artisan serve
```
## Login With
### Superadmin
``` bash
email : mr.silverwind1998@gmail.com
password : admin
```
