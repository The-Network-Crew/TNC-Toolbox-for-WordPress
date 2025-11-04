# Security Policy

## 🏥 Supported Versions

The single latest available release is the only release supported by this Security Policy.

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x latest  | :white_check_mark: |
| 1.2.x   | :x:                |
| 1.1.x   | :x:                |
| 1.0.x   | :x:                |

> \* Note: Latest version - if it is v1.2.3 then that is supported, if latest is v1.4.1 then that is supported, etc. 

## 🔒 Secure Code Contributions

Please refrain from depending on any external source - for instance, noVNC-latest is bundled into the Module.

Best practices should be followed during Software Engineering which is especially hard when only patching.

### For instance, work to implement:

- Principle of Least Privilege
- Input Validation & Sanitisation
- Secure Communication (HTTPS etc)
- Compatibility with core/updates
- Thorough inline comments/specs
- Robust error handling/logging
- Secure configs by default

### References:

- https://owasp.org/
- [CERT Secure Coding](https://wiki.sei.cmu.edu/confluence/display/seccode/SEI+CERT+Coding+Standards)
- [NIST Secure SW Dev](https://csrc.nist.gov/Projects/ssdf)
- [MIT Open Security](https://ocw.mit.edu/courses/6-858-computer-systems-security-fall-2014/)

## 🐛 Reporting a Vulnerability

GitHub "Private vulnerability reporting" is enabled for The-Network-Crew/TNC-Toolbox-for-WordPress repo.

Or, use a publicly-available email address for The Network Crew Pty Ltd to submit it via email instead.

DO NOT raise a public issue where there is threat to users of the module. Raise it properly.

### No bounties offered

As a company, we do not believe in paying security bounties, rather in writing good code.

We appreciate your input and work to address issues as quickly as possible, security first and foremost.

Updates can be provided as promptly as days apart, however this depends on severity/scope, and is always reasonable.

## ❤️ Thank you for responsibly disclosing

We & the entire FOSS community thank you for reviewing this file & being aware of how to improve the project.