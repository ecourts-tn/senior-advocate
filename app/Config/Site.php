<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Site / portal metadata (includes GIGW policy content) and policy content.
 * Guidelines for Indian Government Websites and Apps.
 */
class Site extends BaseConfig
{
    /** Displayed on all pages (GIGW: Last Updated Date). */
    public string $lastUpdated = '27 July 2026';

    public string $organisation = 'High Court of Madras';

    public string $portalName = 'Designation of Senior Advocates';

    public string $department = 'High Court of Madras';

    public string $address = 'High Court Buildings, Chennai – 600 104, Tamil Nadu, India';

    public string $email = 'registrar.admin@hcmadras.tn.gov.in';

    public string $phone = '+91-44-2534 2190';

    public string $website = 'https://www.hcmadras.tn.gov.in/';

    /** Content ownership / stewardship line for footer. */
    public string $contentOwnedBy = 'High Court of Madras';

    public string $maintainedBy = 'Registrar (Administration) / Registrar (IT cum Statistics), High Court of Madras';

    /**
     * Policy pages required under GIGW (privacy, security, archival, etc.).
     * Each key is the URL slug.
     *
     * @var array<string, array{title: string, body: list<string>}>
     */
    public array $policies = [
        'accessibility' => [
            'title' => 'Accessibility Statement',
            'body'  => [
                'This portal aims to conform to the Guidelines for Indian Government Websites (GIGW) 3.0 and WCAG 2.1 Level AA, in furtherance of Section 42 of the Rights of Persons with Disabilities Act, 2016.',
                'Features provided for inclusive access include: skip-to-content link, keyboard-accessible navigation, adjustable text size, high-contrast mode, responsive layout for mobile devices, and meaningful alternative text for key images.',
                'If you face difficulty accessing any content or feature, please contact the Permanent Secretariat using the details in the Contact section of the footer. We will endeavour to provide the information in an accessible format.',
                'Screen reader users may use free tools such as NVDA (Windows), VoiceOver (macOS/iOS), TalkBack (Android), or Orca (Linux).',
            ],
        ],
        'privacy' => [
            'title' => 'Privacy Policy',
            'body'  => [
                'This Privacy Policy describes how personal information submitted through the Portal for Designation of Senior Advocates is collected, used, and protected.',
                'Information collected includes registration details (name, email, mobile), application form data as per the official proforma, uploaded documents (photograph, signature, enrolment certificate, Formats L-1 to L-4), and technical logs necessary for security and audit.',
                'Data is used solely for processing applications for designation as Senior Advocate, verification, review by authorised officers of the High Court of Madras, generation of acknowledgements/PDFs, and statutory record-keeping.',
                'Personal data is not sold or shared with third parties for commercial purposes. Access is restricted to authorised users (applicants for their own data; reviewers/administrators as per role).',
                'Reasonable technical and organisational measures are applied to protect data in transit and at rest. Applicants should keep their login credentials confidential.',
                'For queries regarding personal data on this portal, contact the Permanent Secretariat using the contact details published in the footer.',
            ],
        ],
        'terms' => [
            'title' => 'Terms & Conditions',
            'body'  => [
                'By using this portal, you agree to submit true and complete information as required under the Approved Rules for Designation of Senior Advocates by the High Court of Madras, 2026.',
                'Users are responsible for the accuracy of data entered and documents uploaded. Furnishing false information or suppression of material facts may render an applicant unfit for designation.',
                'An error in a submitted application cannot be subsequently rectified and may result in rejection. Late applications beyond the notified last date shall not be entertained.',
                'The High Court of Madras reserves the right to modify portal features, schedules, and processes without prior notice, consistent with applicable rules and notifications.',
                'Unauthorised access, attempts to disrupt service, or misuse of another person’s credentials is prohibited and may attract legal action.',
            ],
        ],
        'copyright' => [
            'title' => 'Copyright Policy',
            'body'  => [
                'Material on this portal—including text, layout, logos, and generated application forms—is owned by the High Court of Madras or used under applicable rights, unless otherwise indicated.',
                'Content may be reproduced for non-commercial personal or professional use with due acknowledgement of the High Court of Madras, provided the material is reproduced accurately and not used in a misleading context.',
                'The High Court emblem / seal and related insignia must not be used without prior written permission of the competent authority.',
                'Requests for use of copyrighted material beyond fair personal use may be addressed to the Registrar (Administration), High Court of Madras.',
            ],
        ],
        'hyperlinking' => [
            'title' => 'Hyperlinking Policy',
            'body'  => [
                'Links to this portal may be created freely for non-commercial purposes without prior permission, provided the link does not misrepresent the High Court of Madras or imply endorsement of the linking site.',
                'This portal may contain links to external websites (for example, the main High Court website). These are provided for convenience. The High Court of Madras is not responsible for the content or availability of external sites.',
                'External links open in a new browsing context where technically indicated; users should verify the authenticity of destinations before sharing sensitive information.',
            ],
        ],
        'content-archival' => [
            'title' => 'Content Archival Policy (CAP)',
            'body'  => [
                'Portal content is reviewed periodically. Notifications, instructions, and rule-related pages are archived when superseded, with the last-updated date displayed as required under GIGW.',
                'Submitted applications and related records are retained as per judicial / administrative record retention norms applicable to the High Court of Madras and are not treated as public web content for open archival on this portal.',
                'Obsolete public pages may be removed from active navigation after archival or replacement with current versions.',
            ],
        ],
        'content-review' => [
            'title' => 'Content Review Policy (CRP)',
            'body'  => [
                'Public-facing content (instructions, policies, home page notices) is reviewed under the supervision of the Registrar (Administration) / Registrar (IT cum Statistics) or officers nominated for the Senior Advocate Designation process.',
                'Content contribution, moderation, and approval follow internal administrative practice: draft → review → approval before publication on this portal.',
                'The “Last Updated” date on pages reflects the most recent content review or material change.',
            ],
        ],
        'contingency' => [
            'title' => 'Contingency Management Plan',
            'body'  => [
                'This plan addresses continuity of the Senior Advocate Designation online portal in the event of service disruption, cyber incident, or infrastructure failure.',
                'Hosting and infrastructure are to be maintained in a secured environment with backup and restore procedures. Critical application data is backed up at defined intervals.',
                'In case of prolonged outage, alternate channels (including physical submission at the Permanent Secretariat, if notified) may be activated by competent authority through official notice.',
                'Security incidents will be handled as per institutional IT security practice and applicable CERT-In directions. Users will be informed of significant service interruptions through the High Court website or official notice, where feasible.',
            ],
        ],
        'security' => [
            'title' => 'Security Policy',
            'body'  => [
                'The portal is intended to be hosted with TLS (HTTPS) encryption for data in transit. Access control is role-based (applicant, reviewer, administrator).',
                'Uploaded files are validated for type and size. Session management and password-based authentication protect user accounts. Applicants must not share passwords.',
                'Security audits and vulnerability assessments (including VAPT by CERT-In empanelled auditors, as mandated for government digital services) shall be undertaken before production launch and periodically thereafter as per institutional schedule.',
                'Suspected security issues may be reported to the Registrar (IT cum Statistics), High Court of Madras, through official channels.',
            ],
        ],
        'website-monitoring' => [
            'title' => 'Website Monitoring Plan',
            'body'  => [
                'Availability, broken links, and critical functionality of the portal are monitored by the IT wing / designated officers.',
                'Application workflow, audit logs, and error logs support operational monitoring. Defects affecting applicants are prioritised for remediation.',
                'Feedback on portal issues may be sent using the contact details published in the footer.',
            ],
        ],
        'disclaimer' => [
            'title' => 'Disclaimer',
            'body'  => [
                'Content on this portal is published to facilitate online submission of applications for designation as Senior Advocate. In case of any conflict between portal content and the Approved Rules / official notifications of the High Court of Madras, the official Rules and notifications shall prevail.',
                'While reasonable care is taken to keep information accurate and current, the High Court of Madras does not accept liability for any loss or damage arising from use of this portal or reliance on its content, except as required by law.',
                'Temporary unavailability due to technical reasons may occur; applicants should complete submissions well before notified deadlines.',
            ],
        ],
        'help' => [
            'title' => 'Help & Contact',
            'body'  => [
                'For assistance with online application submission, document specifications, or account access, contact the Permanent Secretariat for Senior Advocate Designation.',
                'Email and telephone details are published in the website footer. Official correspondence may also be addressed to the Registrar (Administration), High Court of Madras, Chennai – 600 104.',
                'Before writing for help, please read the Instructions page and ensure scanned documents meet the prescribed size and format limits.',
            ],
        ],
    ];
}
