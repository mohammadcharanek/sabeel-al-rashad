# Project Rules Index

This directory contains persistent project-specific architectural and business rules for Sabeel Al Rashad.

These rules complement the general Laravel, testing, Git, data-safety, and deployment instructions in:

```text
AGENTS.md
```

Before modifying covered areas, read the relevant rule files.

---

# Education Domain

Rule file:

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

Primary topics:

- EducationSystem architecture
- EducationalStage architecture
- EducationalGrade architecture
- Lebanese education compatibility
- American education architecture
- stage/category semantics
- Grade 1–12 American mapping
- educational relationships
- deletion safety
- historical education-data integrity
- homepage education grouping

---

# Student Registration and Admissions

Rule file:

```text
.ai/rules/registration.md
```

Applies to:

```text
app/Models/StudentApplication.php

app/Http/Controllers/StudentApplicationController.php

app/Http/Requests/StoreStudentApplicationRequest.php
app/Http/Requests/**/*StudentApplication*

app/Filament/Resources/StudentApplications/**

resources/views/pages/registration.blade.php
resources/views/**/*registration*

resources/js/app.js

database/migrations/*student_application*
database/migrations/*admission*
database/migrations/*interview*
database/migrations/*exam*

tests/**/*StudentApplication*
tests/**/*Registration*
tests/**/*Admission*
tests/**/*Interview*
tests/**/*EntranceExam*
tests/**/*AmericanRegistration*
```

Primary topics:

- registration flow
- applicant-status semantics
- new-student behavior
- transfer-student behavior
- students arriving from abroad
- education-system selection
- grade selection and compatibility
- school-document requirements
- Grade 10 transfer-document exception
- new-student interview requirements
- entrance-examination requirements
- post-Kindergarten admission rules
- foreign-document authentication/attestation
- server-side validation
- frontend registration behavior
- historical StudentApplication integrity
- admission testing requirements

---

# Rule Interaction

Education-domain rules define:

```text
EducationSystem
→ EducationalStage
→ EducationalGrade
```

Registration rules define:

```text
Applicant Status
+
Selected Education System
+
Selected Grade
+
Admission Requirements
```

Do not mix these concerns unnecessarily.

For example:

```text
New Student
```

is a registration/admission concept.

It is not an EducationalStage category.

Likewise:

```text
American
```

is an EducationSystem.

It is not an applicant type.

---

# General Rules

General rules live in:

```text
AGENTS.md
```

These include:

- Laravel conventions
- Laravel Boost usage
- dependency safety
- PHP conventions
- Pest/testing rules
- Pint formatting
- database/data safety
- Git safety
- deployment safety
- final verification expectations

Do not duplicate general rules in project-specific rule files unless a specific business/domain exception requires it.

---

# Reading Rules Before Changes

Before planning or modifying a covered file:

1. Read `AGENTS.md`.
2. Read this index.
3. Identify all rule files covering the paths/domains involved.
4. Read those rule files.
5. Search `.ai/rules` for relevant domain keywords.

Examples:

```bash
grep -rin "registration" .ai/rules
grep -rin "interview" .ai/rules
grep -rin "entrance" .ai/rules
grep -rin "Grade 10" .ai/rules
grep -rin "American" .ai/rules
```

A task may require reading more than one rule file.

For example, changing American Grade 10 registration may require both:

```text
.ai/rules/education.md
```

and:

```text
.ai/rules/registration.md
```

---

# Adding New Rules

Do not create a permanent project rule for every implementation detail.

Permanent rules should represent stable decisions such as:

- business requirements
- architectural decisions
- compatibility constraints
- historical-data constraints
- non-obvious behavior future developers must preserve

Do not create permanent rules merely for temporary instructions such as:

```text
change this label
fix this spacing
move this button
```

When a stable project decision changes, update the existing relevant rule instead of adding contradictory rules.