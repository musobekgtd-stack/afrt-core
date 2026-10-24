# Changelog

All notable changes to the `afrt-core` library will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - Phase 2 Standalone Implementation

### Added
- Core standalone computational services:
  - `WorkloadCalculator`: statutory teaching and supervisory workload aggregation.
  - `CohortStreamBundler`: lecture stream pooling, subgroup generation, and Taqsimot group slicing.
  - `WorkloadValidator`: FTE quota ceiling validation, saturation warnings, and department-wide checks.
  - `SubjectDiscrepancyMatcher`: cross-registry title reconciliation (exact, normalized, subpart, fuzzy, unmatched).
- Configuration & Value Objects:
  - `AcademicNormConfig`: customizable norms (base quota, thesis hours, thresholds).
  - `CohortGroup`: immutable student cohort group representation.
- Comprehensive DTOs:
  - `CurriculumSubject`, `WorkloadBreakdown`, `StreamPartition`, `FacultyMember`, `AllocationRequest`, `ValidationResult`, `DiscrepancyItem`, `DiscrepancyReport`.
- 100% Synthetic fixtures for undergraduate, graduate, cohorts, faculty, and timetable discrepancies.
- Zero-dependency autoloader (`src/autoload.php`) and standalone test runner (`tests/run_tests.php`).
- Unit and integration test suites covering all 23 test plan cases.
- Four standalone executable example scripts.
- Technical documentation covering algorithms, architecture, and statutory norms.
