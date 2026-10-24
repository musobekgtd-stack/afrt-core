# Algorithmic Specification: AFRT Core

This document specifies the exact computational formulas, rounding rules, boundary conditions, and invariants implemented in `afrt-core`.

---

## 1. Workload Calculation (`WorkloadCalculator`)

The total workload $H_{\text{total}}$ for a curriculum subject across student cohorts is computed as:

$$H_{\text{total}} = H_{\text{aud}} + H_{\text{non\_aud}}$$

### 1.1 Auditorium Hours ($H_{\text{aud}}$)

Auditorium hours represent direct classroom instruction hours:

$$H_{\text{aud}} = H_{\text{lec}} + H_{\text{prac}} + H_{\text{lab}} + H_{\text{sem}}$$

Where:
- **Lecture Hours ($H_{\text{lec}}$)**: Scales with pooled lecture streams ($S$):
  $$H_{\text{lec}} = \text{round}(h_{\text{lec}} \times S, 2)$$
- **Practical Hours ($H_{\text{prac}}$)**: Scales with academic groups ($G$):
  $$H_{\text{prac}} = \text{round}(h_{\text{prac}} \times G, 2)$$
- **Laboratory Hours ($H_{\text{lab}}$)**: Scales with laboratory subgroups ($L$):
  $$H_{\text{lab}} = \text{round}(h_{\text{lab}} \times L, 2)$$
- **Seminar Hours ($H_{\text{sem}}$)**: Scales with academic groups ($G$):
  $$H_{\text{sem}} = \text{round}(h_{\text{sem}} \times G, 2)$$

### 1.2 Non-Auditorium Hours ($H_{\text{non\_aud}}$)

Non-auditorium hours encompass enterprise practice, supervisory, examination, and research guidance activities:

$$H_{\text{non\_aud}} = H_{\text{ent}} + H_{\text{course}} + H_{\text{thesis}} + H_{\text{exam}} + H_{\text{intern}} + H_{\text{doc}}$$

Where:
- **Enterprise Practice ($H_{\text{ent}}$)**: $h_{\text{ent}} \times G$
- **Coursework Guidance ($H_{\text{course}}$)**: Scales with individual student count ($N$):
  $$H_{\text{course}} = \text{round}(h_{\text{cw\_per\_student}} \times N, 2)$$
- **Graduation Thesis Supervision ($H_{\text{thesis}}$)**:
  $$H_{\text{thesis}} = \text{round}((N_{\text{bach}} \times \text{norm}_{\text{bach}}) + (N_{\text{mast}} \times \text{norm}_{\text{mast}}), 2)$$
  Standard default norms: $\text{norm}_{\text{bach}} = 15.0\text{ h}$, $\text{norm}_{\text{mast}} = 25.0\text{ h}$.
- **Doctoral Supervision ($H_{\text{doc}}$)**:
  $$H_{\text{doc}} = \text{round}(N_{\text{phd}} \times \text{norm}_{\text{phd}}, 2) \quad (\text{standard } 50.0\text{ h/candidate})$$
- **Fixed Exam and Internship Hours**: As stipulated in the curriculum plan.

---

## 2. Cohort Stream Bundling (`CohortStreamBundler`)

### 2.1 Stream Pooling Constraints
Let $C = \{g_1, g_2, \dots, g_m\}$ be a collection of student cohort groups sharing identical language medium, education level, and curriculum semester.
Groups are sequentially aggregated into stream $S_k$ such that:

$$\sum_{g \in S_k} |g| \le \text{maxStreamSize} \quad \text{and} \quad |S_k| \le \text{maxGroupsPerStream}$$

Whenever adding group $g_{i+1}$ violates either inequality, stream $S_k$ is sealed and stream $S_{k+1}$ is initiated.

### 2.2 Subgroup Splitting Rule
For each group $g$:
$$\text{subgroups}(g) = \begin{cases} \{g\text{-A}, g\text{-B}\} & \text{if } |g| > \text{threshold} \text{ (default 20)} \\ \{g\} & \text{otherwise} \end{cases}$$

### 2.3 Taqsimot Group Slicing Algorithm
Given $N$ group names and a vector of requested teacher allocation counts $A = [a_1, a_2, \dots, a_k]$:
- Slices groups consecutively: teacher $j$ receives $a_j$ groups starting from pointer $p$.
- If $\sum a_j > N$, the pointer executes deterministic modulo wrap-around ($p \pmod N$).

---

## 3. Workload Validation (`WorkloadValidator`)

### 3.1 Faculty Quota Capacity
For faculty member with appointment rate $\text{FTE}$ (stavka):
$$Q_{\max} = \text{round}(\text{FTE} \times \text{baseQuota}, 2) \quad (\text{default baseQuota} = 750.0\text{ h})$$

### 3.2 Evaluation Rules
- **Capacity Breach**: If $H_{\text{resulting}} > Q_{\max}$, the allocation is rejected with a formal violation.
- **Appointment Limit**: If $\text{FTE} > \text{maxFte}$ (default 1.50), the profile violates appointment ceilings.
- **Saturation Threshold**: If $H_{\text{resulting}} \le Q_{\max}$ and $\frac{H_{\text{resulting}}}{Q_{\max}} \ge 0.95$, a near-saturation warning is issued.

---

## 4. Discrepancy Matching Pipeline (`SubjectDiscrepancyMatcher`)

Reconciliation follows a strict 5-stage deterministic cascade:
1. **Exact Raw Match**: $T_{\text{curr}} \equiv T_{\text{ext}}$ (score: 1.00)
2. **Normalized Match**: $\text{norm}(T_{\text{curr}}) \equiv \text{norm}(T_{\text{ext}})$ after Cyrillic-to-Latin homoglyph mapping, apostrophe normalization, and Roman numeral stripping (score: 0.98)
3. **Subpart-Partitioned Match**: Base subjects match after extracting part/module designations (e.g. `1-qism`, `modul 2`) (score: 0.90)
4. **Fuzzy Blended Match**: Hybrid Levenshtein + Similar Text similarity score $\ge 0.80$
5. **Unmatched**: No counterpart found above threshold.
