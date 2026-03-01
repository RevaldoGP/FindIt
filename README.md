# FindIt – Campus Lost Item Claim System

FindIt is a web-based lost item claim system designed for campus environments.  
The system allows users to browse found items and submit ownership claims if they recognize their lost belongings.

Unlike traditional lost & found systems, users cannot submit lost item reports.  
Instead, they can only search for items that have already been found and uploaded to the system.

---

##  Core Concept

FindIt focuses on **claim verification**, not lost item reporting.

Workflow:

1. Admin uploads found item data.
2. Users browse available found items.
3. If a user recognizes their item, they submit a claim request.
4. Admin reviews the claim details.
5. Admin approves or rejects the claim.
6. Item status is updated accordingly.

---

##  Features

- User Registration & Login
- Role-Based Access (User & Admin)
- Found Item Management (Admin Only)
- Item Image Upload
- Item Status Tracking (Available / Claimed)
- Claim Submission System
- Claim Verification Process

---

##  Tech Stack

**Frontend**
- HTML
- CSS
- JavaScript

**Backend**
- PHP

**Database**
- MySQL

---

##  How to Use the System

### 👤 User Flow

1. Register an account.
2. Login to the system.
3. Browse the list of found items.
4. Click on an item to view detailed information.
5. If the item belongs to you, click **Submit Claim**.
6. Fill in claim details:
   - Item description proof
   - Additional identifying information
7. Wait for admin verification.
8. Check claim status in your dashboard.

---

### 🛠 Admin Flow

1. Login as Admin.
2. Add new found item:
   - Item name
   - Description
   - Location found
   - Upload item image
3. Manage claim requests.
4. Approve or reject claims.
5. Update item status automatically.

---

## 📁 Folder Explanation

###  database/

Contains the SQL file required to set up the database structure.

- `FindIt.sql` includes:
  - Tables for users
  - Tables for items
  - Tables for claims
  - Relationships and constraints

To run the system:
Import this file into MySQL before starting the project.

---

###  uploads/

This folder stores uploaded images of found items.

How it works:

- When user uploads an found report with image:
  - The image file is stored inside `/uploads/`
  - The database saves only the file name or file path
- When displaying items:
  - The system retrieves the image path from database
  - The image is loaded dynamically from `/uploads/`

This approach:
- Keeps database lightweight
- Separates file storage from structured data
- Improves scalability

---

##  Installation Guide

1. Clone repository:
   git clone https://github.com/RevaldoGP/FindIt.git

2. Create a new MySQL database.

3. Import:
   database/FindIt.sql

4. Configure database connection inside your PHP file (usually config or connection file).

5. Run using local server (XAMPP / Laragon).

---

##  UI/UX Prototype

Figma:
https://www.figma.com/design/EXFcAJP9DF6e3cYZhSWaQP/FindIt

---

##  Author

Revaldo Gracio  
Informatics Engineering Student  
Universitas Esa Unggul
