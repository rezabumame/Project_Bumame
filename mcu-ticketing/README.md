# Bumame Ticketing System - Bumame Cahaya Medika

## Overview
This is a comprehensive ticketing dashboard for managing MCU projects, facilitating workflows across Sales, Operations, Procurement, and Medical Results teams.

## Tech Stack
- **Backend**: PHP 8.x (Native MVC Architecture)
- **Database**: MySQL
- **Frontend**: HTML5, Bootstrap 5, jQuery
- **Libraries**: Flatpickr (Datepicker), SortableJS (Kanban), DataTables, Select2

## Installation Guide

1. **Clone/Copy Project**
   - Ensure the folder `mcu-ticketing` is inside `htdocs`.
   - Path: `C:\xampp\htdocs\Project_Bumame\mcu-ticketing`

2. **Database Setup**
   - Open PHPMyAdmin.
   - Create a new database named `mcu_ticketing`.
   - Import the latest backup file (e.g., `mcu_ticketing_backup_YYYYMMDD.sql`) located in the project root.

3. **Configuration**
   - Check `config/database.php`. The system automatically detects environment (Local vs Hosting).
   - Check `config/constants.php` for `BASE_URL` settings.

4. **Running the App**
   - Open browser: `http://localhost/Project_Bumame/mcu-ticketing/public/`
   - You will be redirected to the login page.

## Accounts (Password for all: `password123`)

| Role | Username | Permissions |
|------|----------|-------------|
| **Superadmin** | `superadmin` | Full Access, User Management |
| **Admin Sales** | `admin_sales` | Create & List Projects, Manage RAB |
| **Admin Ops** | `admin_ops` | Assign Korlap, Vendor Mgmt, Inventory |
| **Manager Ops** | `manager_ops` | Approval (Kanban), Assign Kohas, Inventory Approval |
| **Head Ops** | `head_ops` | Final Approval (Kanban) |
| **Procurement** | `procurement` | Vendor Assignment & PO |
| **Korlap** | `korlap` | Field Execution, Inventory Requests |
| **Koordinator Hasil** | `surat_hasil` | Medical Result Processing (Input/Upload) |

## Key Modules & Features

### 1. Project Management (Sales)
- Create Project Form with dynamic fields and file uploads (SPH).
- Real-time Project List with status tracking.
- Sales Dashboard with calendar view.

### 2. Operations Workflow
- **Kanban Board**: Drag & drop project status management (Manager Ops).
- **Vendor Management**: Assign vendors to projects.
- **Inventory System**: Request consumables/assets, Warehouse management, and stock tracking.
- **RAB & Realization**: Budget planning vs actual realization tracking.

### 3. Medical Results (Surat Hasil)
- **Dashboard**: Track MCU result statuses (Pending, Released, TAT Issues).
- **Assignment System**: 
  - Manager Ops can assign projects/dates to Koordinator Hasil (Kohas).
  - **Batch Assignment**: Assigning a project automatically assigns the user to all MCU dates.
- **TAT Monitoring**: Automatic calculation of Turn Around Time (TAT) deadlines based on project type.
- **Data Input**: Input Checked vs Released pax, handle discrepancies (Selisih), and upload PDF results.

### 4. Technical Details
- **MVC Structure**: Clean separation of Controllers, Models, and Views.
- **Security**: Role-Based Access Control (RBAC) on all routes.
- **Optimization**: Database indexing and query optimization.
- **Compatibility**: PHP 8.0+ compatible (Deprecated functions like `finfo_close` removed).

## Folder Structure
- `config/`: DB connection, constants, autoloaders.
- `controllers/`: Business logic (Auth, Projects, MedicalResults, etc.).
- `models/`: Database interactions (Active Record pattern).
- `views/`: HTML templates organized by module.
- `helpers/`: Utility classes (Router, DateHelper, ViewHelper).
- `public/`: Entry point (`index.php`), Assets (CSS, JS, Images).
- `uploads/`: Secured storage for Project Files, BA, Proofs, etc.

