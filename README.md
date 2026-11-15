
# Office Management System

A simple, responsive web-based **Office Management System** built with **HTML, CSS, JavaScript, PHP, and MySQL** to manage employee records, departments, salaries, and work statuses.

## Features
- Live summary cards for Total Staff, Active in Office, Remote, and On Leave
- Add, edit, and delete employee records
- Real-time search by name, email, or role
- Filter employees by department

## Tech Stack
- **Client-side:** HTML5, CSS3, Vanilla JavaScript (`fetch` API)
- **Server-side:** PHP 8+ (`mysqli` prepared statements)
- **Database:** MySQL / MariaDB

## Folder Structure

    OFFICE-MANAGEMENT/
    ├── index.html
    ├── database.sql
    ├── css/
    │   └── style.css
    ├── js/
    │   └── app.js
    └── php/
        ├── db.php
        └── api.php

## How to Run Locally (XAMPP)
1. Install and open **XAMPP**, then start **Apache** and **MySQL**.
2. Place this folder inside `C:\xampp\htdocs\`.
3. Open `http://localhost/phpmyadmin/`, go to the **SQL** tab, paste `database.sql`, and click **Go**.
4. Open `http://localhost/OFFICE-MANAGEMENT/` in your browser.
