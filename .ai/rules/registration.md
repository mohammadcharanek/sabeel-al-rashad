# Student Registration Rules

These rules define the persistent business rules for student registration and StudentApplication behavior in the Sabeel Al Rashad project.

They apply to:

- applicant status
- education-system selection
- grade selection
- interviews
- entrance examinations
- required school documents
- students arriving from abroad
- transfer students
- Grade 10 transfer requirements
- server-side registration validation
- historical application integrity

---

## Registration Architecture

Registration must preserve the existing StudentApplication architecture and historical application records.

Do not redesign StudentApplication merely to support another education system or a new admission requirement.

The education hierarchy remains:

```text
EducationSystem
    ↓
EducationalStage
    ↓
EducationalGrade
```

Applicant status is a separate concept from all three.

---

# Applicant Status Is Independent From Grade

The student's applicant status describes the student's situation before joining Sabeel Al Rashad.

It must NOT be inferred from the requested educational stage or grade.

In particular:

```text
New Student
```

does NOT mean:

```text
Kindergarten
```

and does NOT mean:

```text
Grade 1
```

A new student may apply to any grade that the school currently allows for registration.

Do not implement assumptions such as:

```text
new student → kindergarten
```

or:

```text
new student → Grade 1
```

unless the school explicitly changes this rule later.

Grade eligibility must instead be determined through the normal:

```text
EducationSystem
→ EducationalStage
→ EducationalGrade
```

relationships and registration availability rules.

---

# Applicant Types

The registration system must preserve the distinction between at least these business situations:

```text
New Student
Transfer Student / Student Coming From Another School
Student Arriving From Abroad
```

Use the project's existing stored values/identifiers when they already exist.

Do not rename persisted applicant-type values merely to match the English wording in this document.

---

# New Student

A new student is a student applying to Sabeel Al Rashad without being classified as:

- a transfer student from another school, or
- a student arriving from abroad.

A new student is not tied to Kindergarten or Grade 1.

A new student may apply to any eligible grade.

## Interview Requirement

Every new student requires an interview.

Conceptually:

```text
New Student
→ Interview required
```

The interview requirement is independent from the selected grade.

Do not limit the interview requirement to Kindergarten or Grade 1.

Do not automatically require an entrance exam merely because the applicant is a new student unless another explicit school rule requires it.

---

# Transfer Student

A transfer student is a student coming from another school.

## Required Document

The student must provide:

```text
إفادة من المدرسة التي جاء منها
```

Conceptually:

```text
Transfer Student
→ Previous-school statement required
```

This applies regardless of education system unless a more specific rule below applies.

---

# Transfer Student Applying to Grade 10

A transfer student coming from another school and applying specifically to Grade 10 may provide either:

```text
إفادة من المدرسة التي جاء منها
```

OR:

```text
شهادة من المدرسة التي جاء منها
```

Therefore:

```text
Transfer Student + Grade 10
→ Previous-school statement OR certificate
```

Do not require both documents.

The application must be accepted when either valid document is provided.

The application must fail the document requirement when neither is provided.

This rule is a Grade 10-specific document exception.

It does not remove any entrance-exam requirement that also applies.

---

# Student Arriving From Abroad

A student arriving from another country must be treated as a distinct applicant situation.

The more specific abroad/traveler rule takes precedence over the ordinary local transfer-document rule.

## Required Foreign Document

The student must provide:

```text
إفادة من البلد الذي جاء منه
```

The document must be:

```text
مصدقة من لبنان
```

Conceptually:

```text
Student Arriving From Abroad
→ Foreign school/country statement required
→ Lebanese authentication/attestation required
```

Do not reduce this case to the ordinary:

```text
Transfer Student
→ Previous-school statement
```

rule.

A student arriving from abroad may also previously have attended another school, but the foreign-document rule is the applicable document rule for this applicant situation.

---

# Interview Rules

## New Students

All new students require an interview.

```text
New Student
→ Interview required
```

This applies regardless of:

- education system
- educational stage
- selected grade

unless the school explicitly changes this rule later.

The application logic must not assume that interviews apply only to Kindergarten.

---

# Entrance Examination Rules

Entrance-exam eligibility depends on:

1. the student's applicant situation, and
2. whether the requested grade is after the Kindergarten stage.

## Kindergarten

Students applying within the Kindergarten stage do not receive an entrance-exam requirement from the transfer/traveler rule defined here.

Do not infer an entrance examination solely because a Kindergarten applicant is coming from another school or from abroad unless another explicit rule exists elsewhere.

## After Kindergarten

A student applying to a grade after Kindergarten requires an entrance examination when the student is either:

```text
Transfer Student
```

or:

```text
Student Arriving From Abroad
```

Therefore:

```text
Transfer Student + After Kindergarten
→ Entrance exam required
```

and:

```text
Student Arriving From Abroad + After Kindergarten
→ Entrance exam required
```

The examination requirement is additional to the relevant document requirement.

For example:

```text
Transfer Student + Grade 5
→ Previous-school statement
→ Entrance exam
```

```text
Student Arriving From Abroad + Grade 7
→ Foreign statement authenticated in Lebanon
→ Entrance exam
```

```text
Transfer Student + Grade 10
→ Previous-school statement OR certificate
→ Entrance exam
```

Do not replace the document requirement with the exam requirement.

Both must be satisfied when both apply.

---

# Definition of "After Kindergarten"

Do not determine "after Kindergarten" using fragile assumptions such as a numeric database ID.

Use the existing education-domain relationships/category data.

The logic should determine whether the selected EducationalGrade belongs to a Kindergarten stage or to a stage after Kindergarten.

Do not hardcode:

```text
grade_id > X
```

or similar ID-based logic.

The rule must continue working if IDs or sort order change.

---

# Admission Requirement Matrix

The intended business behavior is:

| Applicant Situation | Kindergarten | After Kindergarten |
|---|---|---|
| New Student | Interview | Interview |
| Transfer Student | Previous-school statement | Previous-school statement + entrance exam |
| Transfer Student to Grade 10 | Not applicable to Kindergarten | Statement OR certificate + entrance exam |
| Student Arriving From Abroad | Foreign statement authenticated in Lebanon | Foreign statement authenticated in Lebanon + entrance exam |

This matrix expresses the current business rules.

Do not add additional requirements unless they already exist in the project or are explicitly requested.

---

# Requirement Combination Rules

Requirements are cumulative unless explicitly stated otherwise.

For example:

```text
Transfer Student + Grade 10
```

requires:

```text
Statement OR Certificate
+
Entrance Exam
```

because Grade 10 is after Kindergarten.

Similarly:

```text
Student Arriving From Abroad + Grade 12
```

requires:

```text
Foreign statement
+
Lebanese authentication/attestation
+
Entrance Exam
```

A requirement should not silently cancel another applicable requirement.

---

# Requirement Priority

When applicant situations overlap, use the most specific applicable business rule.

For document requirements, recommended precedence is:

```text
1. Student Arriving From Abroad
2. Transfer Student to Grade 10
3. Transfer Student
4. New Student
```

This priority is primarily about determining the correct document requirement.

Interview and entrance-exam requirements must still be evaluated separately.

Do not implement a single precedence branch that accidentally prevents other independent requirements from being applied.

For example:

```text
Transfer Student + Grade 10
```

must not stop processing after determining the Grade 10 document rule.

The system must still determine that an entrance exam is required.

---

# Education System Independence

Applicant type and education system are independent concepts.

Valid combinations may include:

```text
New Student + Lebanese System
New Student + American System

Transfer Student + Lebanese System
Transfer Student + American System

Student Arriving From Abroad + Lebanese System
Student Arriving From Abroad + American System
```

Do not infer applicant type from:

- Lebanese/American system
- stage category
- selected grade

Do not encode admission rules around Lebanese-only category names where a grade/stage relationship can be used instead.

---

# Preferred Registration Flow

The preferred user-facing academic selection flow is:

```text
Choose Education System
        ↓
Choose Grade
```

The EducationalStage should normally be inferred from the selected grade.

Do not require a parent/student to select an EducationalStage separately when the selected grade already determines it.

Applicant status should be collected separately from education-system/grade selection.

---

# Education System and Grade Compatibility

Server-side validation must ensure that the selected grade belongs to the selected active EducationSystem.

Never rely only on JavaScript filtering.

Reject incompatible combinations such as:

```text
American System
+
Lebanese Grade
```

and:

```text
Lebanese System
+
American Grade
```

Also reject where applicable:

- unknown education-system ID
- unknown grade ID
- inactive education system
- inactive educational stage
- incompatible grade/stage relationship
- incompatible grade/system relationship

---

# American Registration

American applicants may select:

```text
Grade 1
Grade 2
Grade 3
Grade 4
Grade 5
Grade 6
Grade 7
Grade 8
Grade 9
Grade 10
Grade 11
Grade 12
```

American stage mapping is defined in:

```text
.ai/rules/education.md
```

Do not duplicate the Grade 1–12 stage-mapping algorithm unnecessarily across unrelated files.

American applicants use the same applicant-status concepts:

```text
New Student
Transfer Student
Student Arriving From Abroad
```

unless the school explicitly defines different American admission rules later.

---

# Lebanese Registration

Existing Lebanese registration behavior and stored data must remain backward-compatible.

Do not modify historical Lebanese applications merely to support the American System.

Existing Lebanese grades and relationships must remain usable.

---

# Server-Side Validation

Admission requirements must be enforced server-side.

Frontend visibility, disabled fields, or JavaScript must never be the only enforcement mechanism.

The server must determine requirements from trusted persisted/request-validated domain data such as:

- applicant status
- selected education system
- selected grade
- grade's educational stage
- stage category/system
- submitted documents
- interview/exam data when represented by the application model

Do not trust hidden frontend fields as the sole source of requirement decisions.

---

# Document Validation

At minimum, server-side validation must enforce:

```text
Transfer Student
→ Previous-school statement required
```

```text
Transfer Student + Grade 10
→ Previous-school statement OR certificate required
```

```text
Student Arriving From Abroad
→ Foreign statement required
→ Lebanese authentication/attestation required
```

If the current schema cannot reliably represent one of these requirements, inspect:

- StudentApplication
- document fields
- uploads
- existing migrations
- Filament application administration

before adding a field.

Do not overload an unrelated field merely to avoid a migration.

Any new persisted field should have a clear business meaning.

---

# Interview Representation

The system must represent the new-student interview requirement cleanly.

Before adding new database columns, inspect whether the application already contains:

- interview status
- interview date
- admission status
- notes
- examination/admission records

Reuse an appropriate existing representation if one exists.

If a new field is required, use a clear domain-specific representation.

Do not infer that an interview occurred merely because the application was submitted.

Registration submission and interview completion are separate concepts unless the current application explicitly models them otherwise.

---

# Entrance Exam Representation

Likewise, entrance-exam requirement and entrance-exam completion/result are separate concepts.

Do not treat:

```text
Entrance exam required
```

as automatically meaning:

```text
Entrance exam passed
```

If the existing application tracks exam state/results, preserve that architecture.

If not, inspect the data model before adding fields.

Avoid creating an unnecessary examination subsystem if a small compatible representation is sufficient.

---

# Registration Form Behavior

When education system changes:

- show only compatible grades
- clear an incompatible previously selected grade
- preserve a selected grade only when it remains compatible

When applicant status or selected grade changes:

- update visible document requirements
- update interview messaging where appropriate
- update entrance-exam messaging where appropriate

Frontend behavior is for UX only.

The server must independently recompute and validate the applicable requirements.

After validation errors:

- restore selected applicant status
- restore selected education system
- restore selected compatible grade
- preserve entered form data and uploaded-document state according to existing Laravel behavior

---

# Registration Ordering

Education systems, stages, and grades must respect configured ordering.

American grades must appear in natural educational order:

```text
Grade 1
Grade 2
Grade 3
...
Grade 12
```

Do not rely on alphabetical sorting that would produce:

```text
Grade 1
Grade 10
Grade 11
Grade 12
Grade 2
```

---

# Historical Applications

Existing StudentApplication records must remain readable and valid.

Do not:

- delete historical applications
- rewrite historical grade IDs unnecessarily
- alter stage IDs
- change existing document paths/references
- force new interview/exam fields onto historical applications in a way that makes old records invalid

New validation requirements apply prospectively to relevant new/updated submissions.

Migration defaults/nullability must account for historical records.

---

# Filament Administration

Where admission information is displayed in Filament, make applicable requirements understandable to administrators.

Where appropriate, administrators should be able to understand:

- applicant type
- education system
- selected grade
- whether an interview is required
- whether an entrance exam is required
- relevant submitted school documents
- foreign-document authentication status when represented

Do not add duplicate stored values when these can be safely derived.

Computed/display-only state should remain derived where practical.

---

# Testing Requirements

When registration behavior changes, add meaningful regression tests.

## New Student

Test:

```text
New Student + Kindergarten
→ interview required
```

```text
New Student + grade after Kindergarten
→ interview required
```

Test explicitly that:

```text
New Student
```

does not force:

```text
Kindergarten
```

or:

```text
Grade 1
```

A new student must be able to choose another eligible grade.

---

## Transfer Student

Test:

```text
Transfer Student + Kindergarten
→ previous-school statement required
→ no entrance exam requirement from this rule
```

Test:

```text
Transfer Student + grade after Kindergarten
→ previous-school statement required
→ entrance exam required
```

---

## Grade 10 Transfer

Test:

```text
Transfer Student + Grade 10 + statement
→ document requirement accepted
```

```text
Transfer Student + Grade 10 + certificate
→ document requirement accepted
```

```text
Transfer Student + Grade 10 + neither
→ rejected
```

and confirm:

```text
Transfer Student + Grade 10
→ entrance exam required
```

---

## Student Arriving From Abroad

Test:

```text
Student Arriving From Abroad + Kindergarten
→ foreign statement required
→ Lebanese authentication required
```

Test:

```text
Student Arriving From Abroad + grade after Kindergarten
→ foreign statement required
→ Lebanese authentication required
→ entrance exam required
```

---

## Education-System Compatibility

Preserve tests for:

```text
American + Lebanese grade → rejected
Lebanese + American grade → rejected
inactive system → rejected
inactive stage → rejected
invalid grade → rejected
```

Applicant type must not determine the education system.

Applicant type must not automatically determine the grade.

---

## Important Grade Boundaries

For American registration, preserve boundary testing around:

```text
Grade 1
Grade 5
Grade 6
Grade 8
Grade 9
Grade 12
```

Also include Grade 10 specifically because it has a transfer-document exception.

---

# Implementation Principle

Keep the implementation as simple as possible while correctly representing these rules.

Do not introduce:

- a complex workflow engine
- a rules-engine package
- unnecessary service layers
- separate admission microservices
- complex polymorphic document architecture

unless the existing project already requires them.

Prefer clear Laravel validation/domain methods with focused tests.

However, avoid duplicating the same admission-rule conditions across controllers, requests, Blade, Filament, and JavaScript.

If reusable domain logic is needed, centralize it in the simplest location consistent with the existing project architecture.