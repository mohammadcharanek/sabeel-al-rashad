const header = document.querySelector('[data-site-header]');
const headerMenu = document.querySelector('[data-header-menu]');
const menuToggle = headerMenu?.querySelector('summary');

if (headerMenu) {
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && headerMenu.open) {
            headerMenu.open = false;
            menuToggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!headerMenu.contains(event.target)) {
            headerMenu.open = false;
        }
    });

    headerMenu.addEventListener('focusout', () => {
        requestAnimationFrame(() => {
            if (!headerMenu.contains(document.activeElement)) {
                headerMenu.open = false;
            }
        });
    });

    window.matchMedia('(min-width: 80rem)').addEventListener('change', (event) => {
        const focusWasInMenu = headerMenu.contains(document.activeElement);
        headerMenu.open = false;

        if (event.matches && focusWasInMenu) {
            header.querySelector('a').focus({ preventScroll: true });
        }
    });
}

document.querySelectorAll('a[href^="#"]').forEach((link) => {
    const target = document.getElementById(link.hash.slice(1));

    if (!target) {
        return;
    }

    link.addEventListener('click', (event) => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
            return;
        }

        if (headerMenu) {
            headerMenu.open = false;
        }

        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
    });
});

const navigationLinks = [...document.querySelectorAll('[data-nav-link]')];
const navigationTargets = [...new Set(navigationLinks.map((link) => document.getElementById(link.hash.slice(1))))].filter(Boolean);
let updatePending = false;

function updateCurrentSection() {
    const offset = (header?.getBoundingClientRect().height ?? 0) + 32;
    let current = null;
    let currentTop = -Infinity;

    navigationTargets.forEach((target) => {
        const top = target.getBoundingClientRect().top;

        if (top <= offset && top > currentTop) {
            current = target.id;
            currentTop = top;
        }
    });

    navigationLinks.forEach((link) => {
        if (link.hash === '#' + current) {
            link.setAttribute('aria-current', 'location');
        } else {
            link.removeAttribute('aria-current');
        }
    });

    updatePending = false;
}

function scheduleSectionUpdate() {
    if (!updatePending) {
        updatePending = true;
        requestAnimationFrame(updateCurrentSection);
    }
}

window.addEventListener('scroll', scheduleSectionUpdate, { passive: true });
window.addEventListener('resize', scheduleSectionUpdate);
window.addEventListener('load', scheduleSectionUpdate);
updateCurrentSection();

const registrationForm = document.querySelector('[data-registration-form]');

if (registrationForm) {
    const requirements = JSON.parse(registrationForm.dataset.requirements);
    const defaultRequirements = JSON.parse(registrationForm.dataset.defaultRequirements);
    const grade = registrationForm.querySelector('#educational_grade_id');
    const educationSystem = registrationForm.querySelector('#education_system_id');
    const gradePlaceholder = grade.options[0];
    const gradeOptions = [...grade.options].slice(1);
    const registrationType = registrationForm.querySelector('#registration_type');
    const documentInput = registrationForm.querySelector('#document');
    const documentLabel = registrationForm.querySelector('#document-requirement');
    const documentSummary = registrationForm.querySelector('[data-document-summary]');
    const interviewSummary = registrationForm.querySelector('[data-interview-summary]');
    const examSummary = registrationForm.querySelector('[data-exam-summary]');
    const documentType = registrationForm.querySelector('#document_type');
    const documentTypeField = registrationForm.querySelector('[data-document-type-field]');
    const attestation = registrationForm.querySelector('#foreign_document_attestation_confirmed');
    const attestationField = registrationForm.querySelector('[data-attestation-field]');

    const updateRequirements = () => {
        const selected = requirements[grade.value]?.[registrationType.value];
        const previousDocumentType = documentType.value;
        const documentTypes = selected?.document_types ?? {};
        if (previousDocumentType && !Object.hasOwn(documentTypes, previousDocumentType)) {
            documentInput.value = '';
        }
        documentType.replaceChildren(new Option('اختر نوع المستند', ''),
            ...Object.entries(documentTypes).map(([value, label]) => new Option(label, value)));
        documentType.value = Object.hasOwn(documentTypes, previousDocumentType) ? previousDocumentType : '';
        documentType.required = selected?.document_required === true;
        documentType.disabled = !documentType.required;
        documentTypeField.hidden = !documentType.required;
        documentType.setAttribute('aria-required', String(documentType.required));
        registrationForm.querySelector('#document_type-requirement').textContent = documentType.required ? '(مطلوب)' : '(اختياري)';
        attestation.required = selected?.attestation_required === true;
        attestation.disabled = !attestation.required;
        attestationField.hidden = !attestation.required;
        attestation.setAttribute('aria-required', String(attestation.required));
        registrationForm.querySelector('#foreign_document_attestation_confirmed-requirement').textContent = attestation.required ? '(مطلوب)' : '(اختياري)';
        if (!attestation.required) {
            attestation.checked = false;
        }
        documentInput.required = selected?.document_required ?? false;
        documentInput.setAttribute('aria-required', String(documentInput.required));
        documentLabel.textContent = documentInput.required ? '(مطلوب)' : '(اختياري)';
        documentSummary.textContent = selected?.document_summary
            ?? defaultRequirements.document_summary;
        interviewSummary.textContent = selected?.interview_summary ?? defaultRequirements.interview_summary;
        examSummary.textContent = selected?.exam_summary
            ?? defaultRequirements.exam_summary;
    };

    grade.addEventListener('change', updateRequirements);
    registrationType.addEventListener('change', updateRequirements);
    const updateGrades = () => {
        const selectedGrade = grade.value;
        const availableOptions = gradeOptions.filter((option) => option.dataset.systemId === educationSystem.value);
        grade.replaceChildren(gradePlaceholder, ...availableOptions);
        grade.value = availableOptions.some((option) => option.value === selectedGrade) ? selectedGrade : '';
        grade.disabled = !educationSystem.value;
        updateRequirements();
    };

    educationSystem.addEventListener('change', updateGrades);
    updateGrades();
}
