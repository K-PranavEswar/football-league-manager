# 🏆 Efootball League Manager

A fully functional **Football League Manager Web App** built using **PHP, MySQL, and Bootstrap**.  
This application allows users to create new leagues, add teams, generate round-robin fixtures, update match results, and automatically calculate league standings and playoffs.

---

## 🚀 Features
- Create or load multiple leagues  
- Add unlimited teams per league  
- Auto-generate double round-robin fixtures  
- View live points table (Wins, Draws, Losses, GD, Points)  
- Enter and update match results dynamically  
- Automatic semi-final and final playoff generation  
- Responsive Bootstrap UI (mobile-friendly)  
- Secure session-based league management  

---

## 🛠️ Tech Stack
- **Frontend:** HTML, CSS, Bootstrap 5  
- **Backend:** PHP (MySQLi, Prepared Statements)  
- **Database:** MySQL (phpMyAdmin)  
- **Server:** XAMPP / WAMP  

---

## 📂 Folder Structure
```
football-league/
├── assets/
│   ├── bootstrap.min.css
│   ├── bootstrap.bundle.min.js
│   └── style.css
├── db.php
├── home.php
├── index.php
├── generate_schedule.php
├── generate_playoffs.php
├── reset.php
└── logout.php
```

---

## ⚙️ Setup Instructions
1. Clone the repository:
   ```bash
   git clone https://github.com/<your-username>/football-league.git
   ```
2. Move the folder to your local server (`htdocs` if using XAMPP).
3. Import the provided SQL schema into phpMyAdmin.
4. Start Apache & MySQL.
5. Visit:
   ```
   http://localhost/football-league/home.php
   ```
6. Create a new league or load an existing one and start managing!

---

## 📸 Screenshots
<img width="1917" height="916" alt="image" src="https://github.com/user-attachments/assets/009083d7-41fd-4c71-8ed6-7286b3023c31" />
<br><br>
<img width="1919" height="907" alt="image" src="https://github.com/user-attachments/assets/2edf75b1-f63a-475b-b120-a1085af6d3ca" />


> Example: Home page, League Table, Schedule Modal, Add Team Popup

---

## 👨‍💻 Developed By
**Pranav Eswar**  
🔗 [LinkedIn](https://www.linkedin.com/in/k-pranav-eswar1/)  
📅 Version 1.0  
📜 License: MIT
