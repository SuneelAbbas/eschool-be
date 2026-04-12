# E-School Platform - Development Tracker

## Project Overview
Building a comprehensive E-School Platform (LMS/ERP) with React + Vite frontend and Laravel backend for Pakistani schools.

---

## Completed Phases

### ✅ Phase 1: User Management
- [x] Registration Flow (BE + FE)
- [x] API Versioning (api/v1 prefix)
- [x] Role Cleanup (admin, teacher, student, parent, accountant, librarian)
- [x] UI/UX Fixes
- [x] Connect existing APIs to FE

### ✅ Database Refactoring (refactor/database-schema branch - TESTED ✅)
- [x] Laravel Sanctum for API authentication
- [x] Normalized database schema with proper foreign keys
- [x] Multi-tenant support (institute_id on tenant tables)
- [x] Institute admin_user_id pattern
- [x] Form Requests for validation
- [x] API Resources for consistent JSON responses
- [x] Role-based middleware (CheckRole, CheckInstituteAccess)
- [x] All controllers updated to use Sanctum auth
- [x] Teacher, Student, Grade, Section, Parent models with relationships
- [x] teacher_section pivot table for assignments
- [x] Fresh migrations working correctly
- [x] Sanctum personal_access_tokens migration
- [x] APIs tested and working:
  - [x] POST /api/v1/institute-register ✅
  - [x] POST /api/v1/login ✅ (403 for pending institutes - correct)

---

## Current Phase

### 🚧 Phase 1.5: FE API Updates for New Schema (feature/fe-api-updates)
- [x] Update User interface (institute_id, full_name)
- [x] Update gradesApi with new routes and response format
- [x] Update sectionApi with new schema
- [ ] Test API integration with FE
- [ ] Wire up SchoolStructurePage to use API hooks

### 🚧 Phase 1.6: Teacher CRUD (FE Integration)
- [ ] Create teacherApi.ts
- [ ] Create TeachersPage.tsx
- [ ] Add teachers to NAV
- [ ] Add route for /teachers

---

## Pending BE API Endpoints

### Grade Management
- [x] GET/POST/PUT/DELETE /api/v1/grades

### Section Management
- [x] GET/POST/PUT/DELETE /api/v1/sections

### Student Management
- [x] GET/POST/PUT/DELETE /api/v1/students
- [ ] Assign student to section
- [ ] Student profile with parent link

### Teacher Management
- [x] GET/POST/PUT/DELETE /api/v1/teachers (API done)
- [ ] Assign teacher to sections
- [ ] FE integration

### Attendance System
- [ ] Attendance model
- [ ] POST/GET/PUT/DELETE /api/v1/attendance
- [ ] Attendance report API
- [ ] FE Attendance UI

### Exam Management
- [ ] Exam types CRUD
- [ ] Exams CRUD
- [ ] Results/Gradebook
- [ ] FE Exam UI

### Fee Management
- [ ] Fee types CRUD
- [ ] Assign fees to students
- [ ] Record payments
- [ ] Fee defaulters
- [ ] FE Fee UI

---

## Tech Stack

### Frontend
- React 18 + Vite
- TypeScript
- Redux Toolkit (RTK Query)
- Tailwind CSS
- React Router

### Backend
- Laravel 11
- MySQL
- Sanctum (Auth)
- API Resources
- Form Requests

### Infrastructure
- Frontend: https://eschools.thedigiorb.com
- Backend API: https://site-eschools.thedigiorb.com/api/v1

---

## API Response Format

### Success Response
```json
{
  "success": true,
  "data": {},
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {}
}
```

---

## Database Schema

```
users ──────────┬── institute_id (FK)
                └── user_type

institutes ─────┼── admin_user_id (FK → users)
                └── plan_id (FK)

teachers ───────┼── institute_id (FK)
                └── user_id (FK → users)

students ───────┼── institute_id (FK)
                ├── user_id (FK → users)
                └── section_id (FK)

teacher_section ├── teacher_id (FK)
                └── section_id (FK)
```

---

## Notes

- **User Roles**: super_admin (BE only), admin, teacher, student, parent, accountant, librarian
- **Registration Flow**: pending → approved/rejected
- **Student-Parent**: 1:1 relationship
- **Fee Frequency**: Monthly + Annual
- **Exam Types**: Unit Tests, Terminal, Annual
- **Attendance**: Daily (MVP approach)

---

## Git Branches

### Backend (eschool-be)
- `master` - Production
- `develop` - Development
- `refactor/database-schema` - DB normalization & Sanctum (READY TO MERGE ✅)

### Frontend (e-school-platform-fe)
- `master` - Production
- `develop` - Development
- `feature/fe-api-updates` - FE API updates for new schema

---

## Next Steps

1. **Merge BE**: `refactor/database-schema` → `develop`
2. **Test FE**: Connect FE to new BE schema
3. **Merge FE**: `feature/fe-api-updates` → `develop`
4. **Phase 1.6**: Teacher CRUD FE integration
5. **Phase 2**: Attendance System
