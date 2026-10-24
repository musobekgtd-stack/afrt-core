# Architecture & Design: AFRT Core

`afrt-core` is structured as a pure, decoupled computational library adhering to Clean Architecture principles.

---

## 1. Package Structure

```
afrt-core/
├── composer.json               # Package definition & autoloading configuration
├── phpunit.xml                 # PHPUnit test runner configuration
├── README.md                   # User guide & research package overview
├── CHANGELOG.md                # Version history
├── CITATION.cff                # Academic citation metadata
├── SECURITY.md                 # Security & reporting policy
├── LICENSE-PENDING.md          # Pending institutional license notice
├── src/
│   ├── autoload.php            # Standalone PSR-4 autoloader
│   ├── Contracts/              # Explicit interfaces defining service boundaries
│   │   ├── CalculatorInterface.php
│   │   ├── BundlerInterface.php
│   │   ├── ValidatorInterface.php
│   │   └── MatcherInterface.php
│   ├── DTO/                    # Immutable data transfer objects
│   │   ├── CurriculumSubject.php
│   │   ├── WorkloadBreakdown.php
│   │   ├── StreamPartition.php
│   │   ├── FacultyMember.php
│   │   ├── AllocationRequest.php
│   │   ├── ValidationResult.php
│   │   ├── DiscrepancyItem.php
│   │   └── DiscrepancyReport.php
│   ├── ValueObjects/           # Immutable domain value objects
│   │   ├── AcademicNormConfig.php
│   │   └── CohortGroup.php
│   ├── Exceptions/             # Domain-specific exceptions
│   │   ├── AfrtCoreException.php
│   │   ├── InvalidCurriculumNormException.php
│   │   ├── InvalidCohortConfigurationException.php
│   │   └── AllocationLimitExceededException.php
│   └── Services/               # Core computational implementations
│       ├── WorkloadCalculator.php
│       ├── CohortStreamBundler.php
│       ├── WorkloadValidator.php
│       └── SubjectDiscrepancyMatcher.php
├── fixtures/
│   └── synthetic/              # 100% Synthetic research test fixtures
│       ├── curriculum_undergraduate.json
│       ├── curriculum_graduate.json
│       ├── cohorts_synthetic.json
│       ├── faculty_synthetic.json
│       └── timetable_discrepancies_synthetic.json
├── examples/                   # Executable standalone example scripts
│   ├── 01_calculate_workload.php
│   ├── 02_bundle_cohort_streams.php
│   ├── 03_validate_faculty_quotas.php
│   └── 04_reconcile_discrepancies.php
├── tests/
│   ├── bootstrap.php
│   ├── run_tests.php           # Standalone pure-PHP test suite runner
│   ├── Fixtures/
│   │   └── SyntheticDepartmentFactory.php
│   ├── Unit/                   # Unit test cases
│   │   ├── WorkloadCalculatorTest.php
│   │   ├── CohortStreamBundlerTest.php
│   │   ├── WorkloadValidatorTest.php
│   │   └── SubjectDiscrepancyMatcherTest.php
│   └── Integration/            # Full department pipeline integration test
│       └── DepartmentWorkflowTest.php
└── docs/                       # Technical specifications & documentation
    ├── algorithms.md
    ├── architecture.md
    └── norms_and_standards.md
```

---

## 2. Decoupling & Isolation Principles

1. **No Framework Imports**: The codebase contains no references to `yii\*`, `Illuminate\*`, or third-party web frameworks.
2. **Zero Database Coupling**: Algorithms do not execute SQL queries or rely on database schemas. Input data is passed as typed DTOs.
3. **Stateless Operations**: All computational services are stateless and deterministic; given identical inputs, they produce identical outputs.
4. **Strict Typing & Invariant Protection**: Every class declares `declare(strict_types=1);` and validates invariants upon construction.
