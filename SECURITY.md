# Security Policy

## Scope & Architectural Posture

`afrt-core` is a computational research library designed to run with **zero external network dependencies**, **zero database access**, and **zero handling of personally identifiable information (PII)**.

Because the library does not process network requests or store state, its primary security considerations pertain to algorithmic stability:
- Deterministic computational termination
- Prevention of integer overflows and negative divisor conditions
- Rejection of malicious, non-sanitized, or malformed DTO payloads

---

## Reporting Vulnerabilities

If you identify a security defect, algorithmic denial-of-service vector, or unintended exposure of institutional logic in this package, please report it privately:

- **Email**: `security-review@example.org` (or directly to the designated institutional contact)
- **Subject**: `[SECURITY VULNERABILITY] afrt-core: <Brief Description>`

Please include:
1. Steps to reproduce or proof-of-concept script
2. Affected version or commit
3. Impact assessment

Do **not** file public issues on public issue trackers until the vulnerability has been triaged and remediated.
