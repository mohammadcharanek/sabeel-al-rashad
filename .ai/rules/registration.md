# Student Registration Rules

These rules apply to student applications and registration behavior.

## Registration Model

Registration must preserve the existing StudentApplication architecture and historical application records.

Do not redesign StudentApplication merely to support another education system.

---

## Preferred Registration Flow

The preferred user-facing flow is:

```text
Choose Education System
        ↓
Choose Grade
```

The EducationalStage should normally be inferred from the selected grade.

Do not require the parent/student to choose a stage when the grade already determines it.

---

## System/Grade Compatibility

Server-side validation must ensure that the selected grade belongs to the selected active EducationSystem.

Never rely only on JavaScript dropdown filtering.

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

Also reject:

- unknown education-system IDs
- unknown grade IDs
- inactive education systems
- inactive educational stages
- other incompatible stage/grade/system relationships

where applicable.

---

## American Registration

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

Stage mapping is defined by the Education Domain rules.

Do not duplicate that mapping in multiple unrelated locations.

---

## Lebanese Registration

Existing Lebanese registration behavior must remain backward-compatible.

Do not alter existing Lebanese admission rules merely because American System support is added.

---

## Admission Requirements

Until separate American admission requirements are explicitly defined, American applicants may use the currently established compatible school-grade requirements.

Do not tightly couple American admission rules to Lebanese category names.

Design validation so American-specific admission requirements can be changed later without rewriting the registration architecture.

Do not introduce a new admission-rules framework unless explicitly required.

---

## Historical Applications

Existing StudentApplication records must remain readable and valid.

Do not:

- delete applications
- rewrite historical grade IDs unnecessarily
- change historical file/document references
- reassign previous applications merely because education-system support was introduced

If education-system information can reliably be inferred from the stored grade/stage relationship, avoid unnecessary duplicated fields.

If an education-system field is persisted as a historical snapshot, consistency must be validated server-side.

---

## Registration Form Behavior

When the education system changes:

- show only compatible grades
- clear an incompatible previously selected grade
- preserve a selected grade only when it remains compatible

After validation errors:

- restore the selected education system
- restore the selected compatible grade
- preserve entered user data according to existing Laravel behavior

JavaScript filtering is a UX enhancement, not the security boundary.

---

## Registration Ordering

Education systems, stages, and grades should respect configured ordering.

American grades must appear in natural educational order:

```text
Grade 1
Grade 2
...
Grade 12
```

Do not rely on alphabetical string ordering where that would produce incorrect order.

---

## Testing Requirements

When registration behavior changes, test meaningful boundaries.

For American registration, important boundary grades include:

```text
Grade 1
Grade 5
Grade 6
Grade 8
Grade 9
Grade 12
```

Also test:

```text
American + Lebanese grade → rejected
Lebanese + American grade → rejected
inactive system → rejected
inactive stage → rejected
invalid grade → rejected
```

Existing Lebanese registration regression tests must continue to pass.