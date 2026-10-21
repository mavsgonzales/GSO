# GSO Property & Inventory Management System

A high-performance, responsive Property, Plant & Equipment (PPE) inventory registry built in **PHP** and **SQL**, specifically designed for General Services Offices (GSO) and Local Government Units (LGUs).

---

## 📋 All 25 Explicitly Displayed Columns

| # | Field Name | Type | Description |
|---|------------|------|-------------|
| 1 | `barcode` | VARCHAR(50) | Scannable asset barcode / QR identifier |
| 2 | `property_number` | VARCHAR(50) | Official GSO property inventory tag number |
| 3 | `article_road_name` | VARCHAR(255) | Name of article, equipment, or road/infrastructure project |
| 4 | `description` | TEXT | Detailed technical specifications, brand, model, serial no. |
| 5 | `onhand_quantity` | DECIMAL(12,2)| Physical count currently on hand |
| 6 | `unit` | VARCHAR(50) | Unit of measure (Choice list provided) |
| 7 | `unit_cost` | DECIMAL(15,2)| Acquisition or unit cost in Philippine Pesos (₱) |
| 8 | `onhand_total_value` | DECIMAL(15,2)| Computed total value (`onhand_quantity * unit_cost`) |
| 9 | `status` | ENUM / TEXT | Physical status (Choice list provided) |
| 10 | `location` | VARCHAR(255) | Physical station, office room, yard, or road section |
| 11 | `accountable` | VARCHAR(255) | Full name of currently accountable employee / officer |
| 12 | `designation` | VARCHAR(255) | Position / title of accountable person |
| 13 | `department` | VARCHAR(255) | Assigned office / department / division (Choice list provided) |
| 14 | `previously_accountable` | VARCHAR(255) | Previous custodian (if asset was transferred) |
| 15 | `acquisition_date` | DATE | Date the asset was accepted / acquired |
| 16 | `date_surrendered` | DATE (Nullable)| Date returned / surrendered to GSO (if any) |
| 17 | `po_number` | VARCHAR(100)| Purchase Order Number |
| 18 | `pr_number` | VARCHAR(100)| Purchase Request Number |
| 19 | `invoice_number` | VARCHAR(100)| Sales Invoice Number |
| 20 | `are_number` | VARCHAR(100)| Inventory Custodian Slip (ICS) number; retained as `are_number` in the database for compatibility |
| 21 | `air_number` | VARCHAR(100)| Acceptance and Inspection Report (AIR) Number |
| 22 | `remarks` | TEXT | Inspection notes, warranty info, or repair status |
| 23 | `supplier` | VARCHAR(255) | Vendor, contractor, or supplier company name |
| 24 | `source_of_fund` | VARCHAR(100)| Funding source (Choice list provided) |
| 25 | `general_ledger` | VARCHAR(100)| COA General Ledger Account Code / Title (Choice list provided) |

---

## 🎯 Predefined Choices Built-In

1. **Status Choices**: `Serviceable`, `Unserviceable`, `Under Repair`, `Surrendered`, `Disposed`, `For Condemnation`, `Transferred`, `Lost / Missing`
   - Records marked `Disposed` are listed in the sidebar's **Disposed Items** view and excluded from the main inventory table.
   - **Inventory Check** scans system-generated QR labels, marks matched items as physically found, and records the latest scan time, user, and scan count. Administrators can filter the list and scans by department; regular users are limited to their assigned department.
2. **Unit Choices**: `unit`, `piece`, `set`, `lot`, `box`, `roll`, `bundle`, `ream`, `meters`, `linear meters`, `sq. meters`, `pairs`, `drum`, `cylinder`, `package`
3. **Department Choices**:
   - General Services Office (GSO)
   - City Engineering Office
   - Office of the City Mayor
   - City Health Office
   - City Disaster Risk Reduction Management Office (CDRRMO)
   - Management Information Systems (MIS)
   - Sangguniang Panlungsod
   - City Accounting Office
   - City Budget Office
   - City Treasurer's Office
   - City Assessor's Office
   - City Planning & Development Office
   - City Agriculture Office
   - City Social Welfare & Development Office (CSWDO)
   - City Legal Office
   - Human Resource Management Office (HRMO)
4. **Source of Fund Choices**:
   - General Fund (GF)
   - 20% Component Development Fund (CDF)
   - Special Education Fund (SEF)
   - Local Disaster Risk Reduction & Management Fund (LDRRMF)
   - Trust Fund
   - Calamity Fund
   - Capital Outlay (CO)
   - National Government Subsidy / Grant
   - Official Development Assistance (ODA)
   - Loan Proceeds
   - Donations
5. **General Ledger Choices (COA Chart of Accounts for PPE)**:
   - 1-07-01-010 Land
   - 1-07-02-010 Land Improvements
   - 1-07-03-010 Infrastructure Assets - Road Networks
   - 1-07-03-020 Infrastructure Assets - Flood Control Systems
   - 1-07-03-030 Infrastructure Assets - Sewer Systems
   - 1-07-03-040 Infrastructure Assets - Water Supply Systems
   - 1-07-04-010 Buildings & Other Structures
   - 1-07-05-010 Machinery & Heavy Equipment
   - 1-07-05-020 Office Equipment
   - 1-07-05-030 Information & Communication Technology (ICT) Equipment
   - 1-07-05-090 Disaster Response and Rescue Equipment
   - 1-07-05-100 Military, Police & Security Equipment
   - 1-07-05-110 Medical Equipment
   - 1-07-05-130 Sports Equipment
   - 1-07-05-140 Technical & Scientific Equipment
   - 1-07-05-990 Other Machinery and Equipment
   - 1-07-06-010 Motor Vehicles
   - 1-07-07-010 Furniture and Fixtures
   - 1-07-08-010 Books
   - 1-07-99-990 Other Property, Plant & Equipment

---

## 🚀 Running the App
```powershell
cd c:\Users\Hi\Desktop\GSO
php -S 127.0.0.1:8000
```
Open [http://127.0.0.1:8000/index.php](http://127.0.0.1:8000/index.php) in your browser.

## 🔐 Accounts and Department Access

Public registration is disabled. Administrators add regular user accounts from the Users page and assign each account a department and temporary password. Regular accounts can view and search inventory only in their assigned department; administrators can access all departments, manage users, and view audit logs. Accounts created by an administrator are regular users by default.

Assign administrator access directly in the configured database (replace the username as needed):

```sql
UPDATE users SET role = 'admin' WHERE username = 'your-admin-username';
```

Accounts created before department selection was added need a department assigned once in the database:

```sql
UPDATE users
SET department = 'General Services Office (GSO)'
WHERE username = 'existing-username';
```

---

## 🏛️ MVC Architecture

Application requests enter through the root PHP entry points, which dispatch to controllers. Controllers handle HTTP input and coordinate models/services; models own database queries; views render the response. Database setup lives in `app/Config/Database.php`. The root-level `auth.php`, `database.php`, `add_modal.php`, and `sidebar.php` are retained as backward-compatibility wrappers and are not used for application request handling.

```text
GSO/
├── app/
│   ├── Config/
│   │   ├── Database.php          # Database connection (SQLite & MySQL) & migrations
│   │   └── Constants.php         # Centralized choices (Status, Units, Departments, Fund, GL, Fields)
│   ├── Core/
│   │   ├── Controller.php        # Base controller: render views, redirect, JSON responses
│   │   └── Auth.php              # Session security, authentication guards, user context
│   ├── Models/
│   │   ├── PropertyModel.php     # 25-field property inventory CRUD, search, stats, suggestions
│   │   ├── UserModel.php         # User authentication, account creation, password management
│   │   └── AuditLogModel.php     # Audit logging and field diff tracking
│   ├── Controllers/
│   │   ├── AuthController.php    # Login, logout, change password
│   │   ├── PropertyController.php# Dashboard ledger, create, edit, delete, suggestions, ICS printing
│   │   ├── InventoryCheckController.php # QR-based physical inventory verification
│   │   ├── UserController.php    # Admin user management
│   │   ├── ReportController.php  # Filterable inventory report generator
│   │   ├── AuditLogController.php# Audit activity logs viewer
│   │   └── QrCodeController.php  # QR code printing
│   ├── Services/
│   │   └── ExcelInventoryImporter.php # Excel workbook parsing
│   ├── Views/
│   │   ├── partials/             # Reusable UI partials (sidebar navigation, alerts)
│   │   ├── auth/                 # Login & Change Password views
│   │   ├── inventory/            # Dashboard table, 25-field Add/Edit modal, ICS print layout
│   │   ├── users/                # User management view
│   │   ├── reports/              # Report viewer & print layout
│   │   └── audit_logs/           # Activity logs table view
│   └── bootstrap.php             # PSR-4 autoloader and app initialization
├── index.php                     # Clean entry point -> PropertyController@index
├── login.php                     # Clean entry point -> AuthController@login
├── logout.php                    # Clean entry point -> AuthController@logout
├── change_password.php           # Clean entry point -> AuthController@changePassword
├── users.php                     # Clean entry point -> UserController@index
├── report.php                    # Clean entry point -> ReportController@index
├── audit_logs.php                # Clean entry point -> AuditLogController@index
├── ics_print.php                 # Clean entry point -> PropertyController@printIcs
├── search_suggestions.php        # Clean entry point -> PropertyController@suggestions
├── qr_print.php                  # Clean entry point -> QrCodeController@printSelected
├── images/                       # Application image assets (logos, seals)
│   ├── logo.png
│   └── logo1.png
└── sidebar.css                   # Global sidebar & navigation styles
```
