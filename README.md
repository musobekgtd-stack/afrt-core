# AFRT Core (afrt-core)

Standalone computational algorithms for academic workload calculation, cohort stream bundling, faculty appointment quota validation, and course discrepancy reconciliation.

[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.23611473.svg)](https://doi.org/10.5281/zenodo.23611473)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.0-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-23%20passed%20%28100%25%29-brightgreen.svg)](tests/)

---

## 1. Overview & Research Scope

**AFRT Core** is a decoupled, standalone research software package providing pure mathematical and algorithmic implementations extracted from the computational kernel of the **Academic Faculty Resource Tracker (AFRT)**.

### Architectural Boundary: AFRT Core vs. Proprietary AFRT Platform

To support reproducible scientific research, software verification, and benchmarking without exposing institutional intellectual property or private databases, **AFRT Core** maintains strict functional separation from the full platform:

| Dimension | AFRT Core (Research Package) | Production AFRT Platform |
| :--- | :--- | :--- |
| **Primary Scope** | Pure deterministic algorithms & formulas | Full institutional administrative ERP |
| **Framework Dependency** | **Zero dependencies** (plain PHP >= 8.0) | Yii2 Web Framework |
| **Database Requirement** | **None** (operates on pure DTOs & memory) | MySQL / MariaDB relational database |
| **Authentication / IAM** | **None** | RBAC, JWT, session authentication |
| **External Credentials** | **None** | Live Ministry HEMIS API integrations |
| **Personal Data / PII** | **Zero** (100% synthetic fixtures only) | Real faculty, staff, and student records |
| **Institutional State** | Fully generalized & parameterized | Specialized to university structure |

---

## 2. Core Modules

1. **`WorkloadCalculator`** (`Afrt\Core\Services\WorkloadCalculator`):
   Computes academic workload hours across auditorium activities (lectures, practicals, labs, seminars) and non-auditorium supervisory duties (theses, coursework, examinations, doctoral mentorship) based on statutory norms.
2. **`CohortStreamBundler`** (`Afrt\Core\Services\CohortStreamBundler`):
   Partitions student cohort groups into pooled lecture streams based on pedagogical capacity constraints, and generates laboratory subgroup divisions. Reproduces the deterministic slicing logic of `Taqsimot::calculateGuruhNomlari`.
3. **`WorkloadValidator`** (`Afrt\Core\Services\WorkloadValidator`):
   Evaluates faculty appointment allocations against full-time equivalent (FTE / stavka) quotas, monitors saturation thresholds, and flags overload breaches.
4. **`SubjectDiscrepancyMatcher`** (`Afrt\Core\Services\SubjectDiscrepancyMatcher`):
   Reconciles curriculum course titles with timetable and external registry feeds through a five-stage pipeline: exact, normalized (homoglyphs & quotes), subpart-partitioned (`1-qism`, `modul 2`), fuzzy Levenshtein similarity, and unmatched detection.

---

## 3. Installation & Requirements

### System Requirements
- **PHP**: `^8.0 || ^8.1 || ^8.2 || ^8.3`
- **Extensions**: `ext-mbstring`

### Installation via Composer (when published)
```bash
composer require afrt/afrt-core
```

### Standalone Zero-Dependency Usage
You can include the built-in autoloader directly without Composer:
```php
require_once __DIR__ . '/src/autoload.php';
```

---

## 4. Minimal Executable Example

```php
<?php

require_once __DIR__ . '/src/autoload.php';

use Afrt\Core\DTO\CurriculumSubject;
use Afrt\Core\Services\WorkloadCalculator;
use Afrt\Core\ValueObjects\AcademicNormConfig;

$calculator = new WorkloadCalculator();
$config = AcademicNormConfig::createDefault();

$subject = new CurriculumSubject(
    code: 'CS-101',
    name: 'Introduction to Computer Systems',
    lectureHours: 36.0,
    practicalHours: 36.0,
    labHours: 36.0,
    courseworkHoursPerStudent: 2.0,
    bachelorThesisStudents: 4
);

// 4 groups of 25 students (100 total) -> 1 stream, 4 groups, 8 lab subgroups
$breakdown = $calculator->calculateSubjectWorkload(
    $subject,
    streamCount: 1,
    groupCount: 4,
    subgroupCount: 8,
    studentCount: 100,
    config: $config
);

echo "Total Auditorium Hours:     " . $breakdown->getAuditoriumHours() . " h\n";
echo "Total Non-Auditorium Hours: " . $breakdown->getNonAuditoriumHours() . " h\n";
echo "Total Computed Workload:    " . $breakdown->getTotalHours() . " h\n";
```

For additional examples, see the [examples/](examples/) directory.

---

## 5. Running Tests

The test suite can be run using PHPUnit or the standalone runner:

```bash
# Option A: Standalone Runner (Zero dependencies)
php tests/run_tests.php

# Option B: PHPUnit
phpunit -c phpunit.xml
```

---

## 6. Assumptions & Limitations

1. **Deterministic Slicing**: Slicing distributes non-divisible groups by index ordering.
2. **Statutory Baseline**: Default supervisory norms reflect statutory standards (15h/bachelor thesis, 25h/master thesis, 50h/doctoral candidate). These are configurable via `AcademicNormConfig`.
3. **No Database State**: AFRT Core operates strictly in memory. State persistence is the responsibility of the host application.

---

## 7. License & Citation

- **License**: Released under the permissive [MIT License](LICENSE).
- **Persistent DOI**: [![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.23611473.svg)](https://doi.org/10.5281/zenodo.23611473) (`10.5281/zenodo.23611473`)
- **Citation**: See [CITATION.cff](CITATION.cff) for complete BibTeX and academic citation metadata.

```bibtex
@software{afrt_core_2026,
  author       = {{AFRT Research Team}},
  title        = {AFRT Core: Standalone Computational Algorithms for Academic Workload Allocation and Cohort Partitioning},
  year         = 2026,
  publisher    = {Zenodo},
  version      = {v0.1.0-alpha},
  doi          = {10.5281/zenodo.23611473},
  url          = {https://doi.org/10.5281/zenodo.23611473}
}
```
