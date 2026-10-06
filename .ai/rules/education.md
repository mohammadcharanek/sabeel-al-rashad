# Education Domain Rules

These are persistent architectural rules for the Sabeel Al Rashad education domain.

## Domain Hierarchy

Education-system architecture follows:

```text
EducationSystem
    ↓
EducationalStage
    ↓
EducationalGrade
```

Do not collapse Education System and Educational Stage Category into the same concept.

---

## Education Systems

The application supports multiple education systems.

Current systems include:

```text
Lebanese / National System
American System
```

The architecture must remain extensible enough to support additional systems later without redesigning the education domain.

Possible future systems may include:

```text
French
British
IB
```

Do not implement future systems unless explicitly requested.

Use stable machine-readable identifiers/slugs for business logic.

Do not use translated display labels as identifiers.

---

## Existing Lebanese System

Existing Lebanese educational stages and grades are historical application data and must remain backward-compatible.

Do not change existing:

- stage IDs
- grade IDs
- grade codes
- student-application relationships
- stored category values

without an explicit migration requirement.

Existing supported stage categories include values such as:

```text
kindergarten
basic
primary
intermediate
secondary
```

Do not remove or rename legacy values merely to normalize naming.

---

## Category vs Education System

`education_system` and stage `category` are separate concepts.

Examples:

```text
Education System: Lebanese
Category: secondary
```

and:

```text
Education System: American
Category: high_school
```

Do not use:

```text
american
```

as a replacement for an EducationalStage category.

---

## American System

The American system supports Grade 1 through Grade 12.

The stage structure is:

```text
American System

Elementary School
├── Grade 1
├── Grade 2
├── Grade 3
├── Grade 4
└── Grade 5

Middle School
├── Grade 6
├── Grade 7
└── Grade 8

High School
├── Grade 9
├── Grade 10
├── Grade 11
└── Grade 12
```

The boundaries are authoritative:

```text
Grade 1–5  → Elementary
Grade 6–8  → Middle School
Grade 9–12 → High School
```

Do not turn Grade 1–12 into twelve EducationalStage records.

They are EducationalGrade records.

---

## American Grade Codes

American grade codes must remain globally distinguishable from existing Lebanese grade codes.

Use the established convention:

```text
american_grade_1
american_grade_2
...
american_grade_12
```

Do not replace existing Lebanese codes such as:

```text
grade_1
```

---

## Relationships

Prefer deriving a grade's education system through:

```text
EducationalGrade
    → EducationalStage
    → EducationSystem
```

Do not duplicate `education_system_id` onto EducationalGrade unless a demonstrated business requirement requires it.

---

## Stage Identity Safety

An EducationalStage that is already referenced by grades, applications, or historical records must not have identity-defining relationships changed in a way that creates inconsistent historical data.

Changing an existing stage's education system must be blocked or safely validated when dependent records exist.

---

## Deletion

Do not cascade-delete educational data merely to avoid foreign-key errors.

If an EducationalStage has dependent grades or historical applications:

- block deletion
- preserve records
- show a clear administrator notification

If an EducationSystem contains stages:

- block deletion
- preserve stages
- show a clear administrator notification

Bulk deletion must not silently create partial/destructive outcomes.

---

## Homepage

When stages from multiple education systems are displayed on the homepage:

- group stages by education system
- hide inactive systems
- hide inactive stages
- preserve configured system ordering
- preserve configured stage ordering
- do not render empty system sections
- preserve legacy stage icons/artwork

Avoid unrelated frontend redesigns when changing education-domain behavior.

---

## Seeds and Data Setup

Education-system/stage/grade seeders must be idempotent where practical.

Prefer:

```php
updateOrCreate()
```

or another duplicate-safe strategy.

Never use seeders to:

- truncate existing educational data
- delete Lebanese stages
- recreate historical grades
- change existing IDs

American system setup must coexist with existing Lebanese data.