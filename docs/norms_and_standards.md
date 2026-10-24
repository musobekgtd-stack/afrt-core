# Statutory Norms & Configuration Standards: AFRT Core

This document outlines the statutory academic workload norms, appointment ratios, and configurable thresholds parameterized in `afrt-core`.

---

## 1. Statutory Workload Norms

The default constants in `AcademicNormConfig` correspond to higher education academic norms:

| Norm Parameter | Default Value | Statutory Origin / Rule | Configurable Property |
| :--- | :--- | :--- | :--- |
| **Base Quota per FTE (Stavka)** | `750.0 hours` | Standard annual academic workload for full-time faculty member (1.0 stavka) | `baseQuotaPerFte` |
| **Maximum Permissible FTE** | `1.50 FTE` | Maximum allowed appointment per teacher (1.0 main + 0.5 internal part-time) | `maxFtePerFaculty` |
| **Bachelor Thesis Supervision** | `15.0 hours/student` | Statutory hourly guidance allowance per undergraduate graduation project | `bachelorThesisNorm` |
| **Master Thesis Supervision** | `25.0 hours/student` | Statutory hourly guidance allowance per graduate master dissertation | `masterThesisNorm` |
| **Doctoral Candidate Supervision** | `50.0 hours/student` | Research advisor hourly quota per doctoral researcher (PhD/DSc) | `phdSupervisionNorm` |
| **Coursework Guidance** | `2.0 - 3.0 hours/student` | Curricular coursework evaluation rate | `courseworkNormPerStudent` |
| **Subgroup Division Threshold** | `20 students` | Cohorts strictly greater than 20 students divide into 2 lab subgroups | `subgroupStudentThreshold` |
| **Max Stream Student Capacity** | `120 students` | Pedagogical ceiling for combined lecture halls | `defaultStreamMaxStudents` |
| **Max Groups per Stream** | `4 groups` | Administrative limit for pooled cohort streams | `defaultStreamMaxGroups` |

---

## 2. Parameterization and Institutional Overrides

Institutions adopting different standard hours or pedagogical guidelines can supply an instance of `AcademicNormConfig` to the computational services:

```php
$customConfig = new AcademicNormConfig(
    baseQuotaPerFte: 800.0,
    maxFtePerFaculty: 1.25,
    bachelorThesisNorm: 18.0,
    masterThesisNorm: 30.0,
    courseworkNormPerStudent: 3.0,
    phdSupervisionNorm: 60.0,
    subgroupStudentThreshold: 25,
    defaultStreamMaxStudents: 150,
    defaultStreamMaxGroups: 5,
    roundingPrecision: 2
);
```

When no custom configuration is provided, `AcademicNormConfig::createDefault()` supplies the verified statutory defaults.
