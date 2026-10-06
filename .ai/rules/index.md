# Project Rules Index

This directory contains persistent project-specific architectural and business rules.

Read the relevant rule files before modifying matching areas.

## Education Domain

File:

```text
.ai/rules/education.md
```

Applies to:

```text
app/Models/EducationSystem.php
app/Models/EducationalStage.php
app/Models/EducationalGrade.php

app/Filament/Resources/EducationSystems/**
app/Filament/Resources/EducationalStages/**
app/Filament/Resources/EducationalGrades/**

database/migrations/*education*
database/migrations/*educational_stage*
database/migrations/*educational_grade*

database/factories/EducationSystemFactory.php
database/factories/EducationalStageFactory.php
database/factories/EducationalGradeFactory.php

database/seeders/*Education*
database/seeders/*Educational*

tests/**/*EducationSystem*
tests/**/*EducationalStage*
tests/**/*EducationalGrade*
tests/**/*American*
```

Topics:

- education-system architecture
- Lebanese education compatibility
- American education architecture
- stage/category semantics
- grade relationships
- deletion/data integrity

---

## Student Registration

File:

```text
.ai/rules/registration.md
```

Applies to:

```text
app/Models/StudentApplication.php
app/Http/Controllers/StudentApplicationController.php
app/Http/Requests/StoreStudentApplicationRequest.php

app/Filament/Resources/StudentApplications/**

resources/views/pages/registration.blade.php
resources/js/app.js

tests/**/*StudentApplication*
tests/**/*Registration*
```

Topics:

- registration flow
- education-system selection
- grade compatibility
- historical application integrity
- server-side validation
- admission requirements

---

## General Rules

General Laravel, Git, data-safety, testing, and deployment rules are defined in:

```text
AGENTS.md
```

Do not duplicate general rules here unless a project-specific exception exists.