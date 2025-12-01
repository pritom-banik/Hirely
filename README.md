# Hirely – Job Portal Database System
Hirely is a full-stack job portal web application designed to connect job seekers with employers through a clean interface and a robust database system. 
Built using HTML, Tailwind CSS, JavaScript, PHP, and an RDBMS (MySQL), this project demonstrates real-world implementation of CRUD operations, user authentication, and relational database design.
This project was developed as part of the **3rd Year 1st Semester Database Course**.

The platform supports two primary user roles:
- ***Company Admins*** — who manage companies and job postings
- ***Job Seekers (Users)*** — who browse and apply for jobs

### Features
#### Company Admin Features
- Registration & Authentication — Secure login system
- Company Management — Create and update company profiles
- Job Posting — Publish job circulars with titles, descriptions, and deadlines
- Applicant Review — View and manage job applications
- Profile Management — Update company details and location

#### Job Seeker Features
- User Registration & Login — Secure account handling
- Profile Customization — Add personal and professional details
- Skills Management — Add or update multiple skills
- Experience Tracking — Manage work history with roles and company names
- Job Search — View job circulars from various companies
- Application Submission — Apply to jobs quickly and easily

## Database Schema
| Entity            | Attributes                                                   | Description                       |
| ----------------- | ------------------------------------------------------------ | --------------------------------- |
| **Company**       | `c_id (PK)`, `name`, `description`, `location`               | Companies posting jobs            |
| **Company_admin** | `admin_id (PK)`, `name`, `email`, `password`, `c_id (FK)`    | Admins managing company data      |
| **Job_circular**  | `j_id (PK)`, `c_id (FK)`, `title`, `description`, `deadline` | Job vacancies posted by companies |
| **User**          | `u_id (PK)`, `name`, `email`, `password`, `summary`          | Job seekers                       |
| **Skill**         | `s_id (PK)`, `u_id (FK)`, `skill_name`                       | User skills                       |
| **Experience**    | `e_id (PK)`, `u_id (FK)`, `role`, `c_name`                   | User work history                 |
| **Application**   | `a_id (PK)`, `j_id (FK)`, `u_id (FK)`                        | Job applications                  |



### Relationships

***Company_admin → Company***: One-to-One<br>
***Company_admin → Job_circular***: One-to-Many<br>
***User → Skill***: One-to-Many<br>
***User → Experience***: One-to-Many<br>
***User → Application***: One-to-Many<br>
***Job_circular → Application***: One-to-Many<br>


## Tech Stack
### Frontend
- HTML5
- Tailwind CSS
- JavaScript
### Backend
- PHP (Core PHP)
- MySQL
### Other Tools
- XAMPP
- phpMyAdmin
- GitHub


## Screenshots

| Image Name | Screenshot |
|------------|------------|
| `hirely_index.php` | <img src="https://github.com/user-attachments/assets/3bcbaef5-82ac-4c98-aa3a-a59099564c06" width="300"> |
| `hirely_register.php` | <img src="https://github.com/user-attachments/assets/1a17dbe0-4a58-417d-86a0-519709dd34f3" width="300"> |
| `hirely_users.php` | <img src="https://github.com/user-attachments/assets/cca9129b-99d0-4128-9d48-9c99bc5f42b7" width="300"> |
| `hirely_admin.php` | <img src="https://github.com/user-attachments/assets/30305f4a-01bd-4c18-8b10-39f754bd105d" width="300"> |
