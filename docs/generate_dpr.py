#!/usr/bin/env python3
"""Generate a simple Detailed Project Report (DPR) for the Senior Advocate Designation Portal."""

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT, TA_RIGHT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    FrameBreak,
    HRFlowable,
    Image,
    KeepTogether,
    ListFlowable,
    ListItem,
    NextPageTemplate,
    PageBreak,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
)
from reportlab.graphics.shapes import Drawing, Rect, String, Line, Polygon
from PIL import Image as PILImage
from reportlab.lib.utils import ImageReader

NAVY = colors.HexColor("#0f2340")
NAVY2 = colors.HexColor("#1a3558")
BRASS = colors.HexColor("#a9792c")
CREAM = colors.HexColor("#f5f2ea")
PAPER2 = colors.HexColor("#ede8da")
LINE = colors.HexColor("#d9d2c5")
MUTED = colors.HexColor("#6b6558")
WHITE = colors.white
BLACK = colors.HexColor("#232323")

PAGE_W, PAGE_H = A4
LEFT = 18 * mm
RIGHT = 18 * mm
TOP = 22 * mm
BOTTOM = 18 * mm
CONTENT_W = PAGE_W - LEFT - RIGHT

SEAL = "/tmp/mhc-seal.png"
LOGO_SRC = "/var/www/html/senior-advocate/public/assets/img/logo-mhc.png"
OUT = "/var/www/html/senior-advocate/docs/DPR-Senior-Advocate-Designation-Portal.pdf"


def prepare_seal():
    """Crop the circular MHC emblem from the wide banner logo."""
    im = PILImage.open(LOGO_SRC).convert("RGBA")
    # Emblem occupies the left ~106 px; beyond that the dark wordmark begins.
    im.crop((0, 0, 106, im.size[1])).save(SEAL)


def header_footer(canvas, doc):
    if canvas.getPageNumber() == 1:
        return
    canvas.saveState()
    canvas.setFillColor(NAVY)
    canvas.rect(0, PAGE_H - 12 * mm, PAGE_W, 12 * mm, fill=1, stroke=0)
    canvas.setFillColor(BRASS)
    canvas.rect(0, PAGE_H - 12.8 * mm, PAGE_W, 0.8 * mm, fill=1, stroke=0)
    canvas.setFillColor(WHITE)
    canvas.setFont("Times-Roman", 8)
    canvas.drawString(
        LEFT,
        PAGE_H - 8 * mm,
        "High Court of Madras  |  Portal for Designation of Senior Advocates",
    )
    canvas.drawRightString(PAGE_W - RIGHT, PAGE_H - 8 * mm, "Detailed Project Report")

    canvas.setFillColor(NAVY)
    canvas.rect(0, 0, PAGE_W, 12 * mm, fill=1, stroke=0)
    canvas.setFillColor(BRASS)
    canvas.rect(0, 12 * mm, PAGE_W, 0.6 * mm, fill=1, stroke=0)
    canvas.setFillColor(WHITE)
    canvas.setFont("Times-Roman", 8)
    canvas.drawString(LEFT, 5 * mm, "Confidential  --  For official use")
    canvas.drawRightString(PAGE_W - RIGHT, 5 * mm, "Page %d" % canvas.getPageNumber())
    canvas.restoreState()


def cover_page(canvas, doc):
    canvas.saveState()
    canvas.setFillColor(NAVY)
    canvas.rect(0, 0, PAGE_W, PAGE_H, fill=1, stroke=0)
    canvas.setFillColor(BRASS)
    canvas.rect(0, PAGE_H - 8 * mm, PAGE_W, 8 * mm, fill=1, stroke=0)
    canvas.rect(0, 0, PAGE_W, 8 * mm, fill=1, stroke=0)

    # inner cream panel
    canvas.setFillColor(CREAM)
    canvas.roundRect(14 * mm, 22 * mm, PAGE_W - 28 * mm, PAGE_H - 44 * mm, 4, fill=1, stroke=0)

    # navy band under seal
    canvas.setFillColor(NAVY)
    canvas.rect(14 * mm, PAGE_H - 92 * mm, PAGE_W - 28 * mm, 22 * mm, fill=1, stroke=0)
    canvas.setFillColor(BRASS)
    canvas.rect(14 * mm, PAGE_H - 93 * mm, PAGE_W - 28 * mm, 1.2 * mm, fill=1, stroke=0)

    canvas.drawImage(SEAL, PAGE_W / 2 - 16 * mm, PAGE_H - 68 * mm, width=32 * mm, height=32 * mm, mask="auto")

    canvas.setFillColor(WHITE)
    canvas.setFont("Times-Bold", 11)
    canvas.drawCentredString(PAGE_W / 2, PAGE_H - 78 * mm, "HIGH COURT OF MADRAS")
    canvas.setFont("Times-Roman", 8.5)
    canvas.drawCentredString(PAGE_W / 2, PAGE_H - 84 * mm, "Permanent Secretariat  |  Senior Advocate Designation")
    canvas.drawCentredString(
        PAGE_W / 2, PAGE_H - 89.5 * mm, "High Court Buildings, Chennai - 600 104, Tamil Nadu"
    )

    canvas.setFillColor(NAVY)
    canvas.setFont("Times-Bold", 10)
    canvas.drawCentredString(PAGE_W / 2, PAGE_H - 108 * mm, "DETAILED PROJECT REPORT")
    canvas.setStrokeColor(BRASS)
    canvas.setLineWidth(1.2)
    canvas.line(PAGE_W / 2 - 28 * mm, PAGE_H - 111 * mm, PAGE_W / 2 + 28 * mm, PAGE_H - 111 * mm)

    canvas.setFillColor(NAVY)
    canvas.setFont("Times-Bold", 16)
    canvas.drawCentredString(PAGE_W / 2, PAGE_H - 124 * mm, "Portal for Designation of")
    canvas.drawCentredString(PAGE_W / 2, PAGE_H - 132 * mm, "Senior Advocates")

    canvas.setFillColor(MUTED)
    canvas.setFont("Times-Italic", 10)
    canvas.drawCentredString(
        PAGE_W / 2,
        PAGE_H - 142 * mm,
        "An online system for the Application-cum-Consent Letter under the",
    )
    canvas.drawCentredString(
        PAGE_W / 2,
        PAGE_H - 147.5 * mm,
        "Rules for Designation of Senior Advocates, 2026",
    )

    # info box
    box_y = 48 * mm
    canvas.setFillColor(WHITE)
    canvas.setStrokeColor(LINE)
    canvas.setLineWidth(0.6)
    canvas.roundRect(28 * mm, box_y, PAGE_W - 56 * mm, 52 * mm, 3, fill=1, stroke=1)

    rows = [
        ("Document type", "Detailed Project Report (abridged)"),
        ("Version / Date", "1.0  |  August 2026"),
        ("Prepared for", "Hon'ble High Court of Madras"),
        ("Steward", "Registrar (IT-cum-Statistics)"),
        ("Content owned by", "Madras High Court"),
        ("Classification", "Official use"),
    ]
    y = box_y + 45 * mm
    for k, v in rows:
        canvas.setFillColor(MUTED)
        canvas.setFont("Times-Bold", 8.5)
        canvas.drawString(34 * mm, y, k)
        canvas.setFillColor(NAVY)
        canvas.setFont("Times-Roman", 9)
        canvas.drawString(78 * mm, y, v)
        y -= 7.4 * mm

    canvas.setFillColor(MUTED)
    canvas.setFont("Times-Roman", 8)
    canvas.drawCentredString(
        PAGE_W / 2,
        32 * mm,
        "This report describes the as-built portal, its modules, technology, security and operations.",
    )
    canvas.restoreState()


def styles():
    ss = getSampleStyleSheet()
    ss.add(
        ParagraphStyle(
            "H1",
            fontName="Times-Bold",
            fontSize=13,
            leading=16,
            textColor=NAVY,
            spaceBefore=10,
            spaceAfter=6,
            borderPadding=0,
        )
    )
    ss.add(
        ParagraphStyle(
            "H2",
            fontName="Times-Bold",
            fontSize=11,
            leading=14,
            textColor=NAVY2,
            spaceBefore=8,
            spaceAfter=4,
        )
    )
    ss.add(
        ParagraphStyle(
            "Body",
            fontName="Times-Roman",
            fontSize=9.5,
            leading=13,
            textColor=BLACK,
            alignment=TA_JUSTIFY,
            spaceAfter=5,
        )
    )
    ss.add(
        ParagraphStyle(
            "BodyLeft",
            fontName="Times-Roman",
            fontSize=9.5,
            leading=13,
            textColor=BLACK,
            alignment=TA_LEFT,
            spaceAfter=4,
        )
    )
    ss.add(
        ParagraphStyle(
            "Cell",
            fontName="Times-Roman",
            fontSize=8.4,
            leading=11.2,
            textColor=BLACK,
        )
    )
    ss.add(
        ParagraphStyle(
            "CellB",
            fontName="Times-Bold",
            fontSize=8.4,
            leading=11.2,
            textColor=WHITE,
        )
    )
    ss.add(
        ParagraphStyle(
            "CellHeadDark",
            fontName="Times-Bold",
            fontSize=8.4,
            leading=11.2,
            textColor=NAVY,
        )
    )
    ss.add(
        ParagraphStyle(
            "Caption",
            fontName="Times-Italic",
            fontSize=8,
            leading=10,
            textColor=MUTED,
            alignment=TA_CENTER,
            spaceBefore=2,
            spaceAfter=8,
        )
    )
    ss.add(
        ParagraphStyle(
            "TOCItem",
            fontName="Times-Roman",
            fontSize=10,
            leading=16,
            textColor=BLACK,
        )
    )
    ss.add(
        ParagraphStyle(
            "Note",
            fontName="Times-Italic",
            fontSize=8.2,
            leading=11,
            textColor=MUTED,
            spaceAfter=6,
        )
    )
    ss.add(
        ParagraphStyle(
            "BulletBody",
            fontName="Times-Roman",
            fontSize=9.5,
            leading=12.5,
            textColor=BLACK,
            leftIndent=12,
        )
    )
    return ss


def p(text, style="Body"):
    return Paragraph(text, S[style])


def cell(text, bold=False, head=False):
    if head:
        return Paragraph(text, S["CellB"])
    if bold:
        return Paragraph(text, S["CellHeadDark"])
    return Paragraph(text, S["Cell"])


def table(data, col_widths, header=True):
    t = Table(data, colWidths=col_widths, repeatRows=1 if header else 0)
    cmds = [
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 5),
        ("RIGHTPADDING", (0, 0), (-1, -1), 5),
        ("TOPPADDING", (0, 0), (-1, -1), 4),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
        ("GRID", (0, 0), (-1, -1), 0.35, LINE),
    ]
    if header:
        cmds += [
            ("BACKGROUND", (0, 0), (-1, 0), NAVY),
            ("TEXTCOLOR", (0, 0), (-1, 0), WHITE),
            ("BACKGROUND", (0, 1), (-1, -1), WHITE),
            ("ROWBACKGROUNDS", (0, 1), (-1, -1), [WHITE, CREAM]),
        ]
    t.setStyle(TableStyle(cmds))
    return t


def kv_table(rows, w1=42 * mm, w2=None):
    w2 = w2 or (CONTENT_W - w1)
    data = [[cell(a, bold=True), cell(b)] for a, b in rows]
    t = Table(data, colWidths=[w1, w2])
    t.setStyle(
        TableStyle(
            [
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 5),
                ("RIGHTPADDING", (0, 0), (-1, -1), 5),
                ("TOPPADDING", (0, 0), (-1, -1), 3.5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 3.5),
                ("GRID", (0, 0), (-1, -1), 0.35, LINE),
                ("BACKGROUND", (0, 0), (0, -1), PAPER2),
                ("BACKGROUND", (1, 0), (1, -1), WHITE),
            ]
        )
    )
    return t


def arch_drawing():
    d = Drawing(CONTENT_W, 72 * mm)
    layers = [
        (54 * mm, NAVY, "Advocates  /  public portal", "Register (enrolment lookup)  |  7-step application  |  track status  |  PDF"),
        (38 * mm, NAVY2, "Permanent Secretariat staff", "Dashboard  |  classification  |  notifications  |  masters  |  audit  |  Excel"),
        (22 * mm, colors.HexColor("#24406e"), "Application layer  /  CodeIgniter 4", "Auth, CSRF, CAPTCHA, uploads, Dompdf, SMTP, SMS gateway"),
        (6 * mm, BRASS, "Data & hosting", "PostgreSQL  |  TLS / HTTPS  |  uploads in writable/ (not public)  |  WAF"),
    ]
    w = CONTENT_W - 4
    for y, col, title, sub in layers:
        d.add(Rect(2, y, w, 14 * mm, rx=3, ry=3, fillColor=col, strokeColor=col))
        d.add(String(10, y + 8.2 * mm, title, fontName="Times-Bold", fontSize=9, fillColor=WHITE))
        d.add(String(10, y + 3.2 * mm, sub, fontName="Times-Roman", fontSize=7.5, fillColor=CREAM))
    return d


S = styles()


def story():
    s = []

    # page 1 is cover (empty flow; drawn on canvas)
    s.append(NextPageTemplate("body"))
    s.append(PageBreak())

    # ----- CONTENTS -----
    s.append(p("Contents", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.8, color=BRASS, spaceAfter=8)
    )
    toc = [
        ("1.", "Project at a glance"),
        ("2.", "Introduction and background"),
        ("3.", "Need for the portal"),
        ("4.", "Objectives"),
        ("5.", "Scope of work"),
        ("6.", "Users and application process"),
        ("7.", "Functional modules"),
        ("8.", "Technical architecture"),
        ("9.", "Security, audit and compliance"),
        ("10.", "Implementation, hosting and support"),
        ("11.", "Indicative cost and schedule"),
        ("12.", "Benefits, risks and recommendation"),
    ]
    rows = []
    for n, t in toc:
        rows.append(
            [
                Paragraph("<b>%s</b>" % n, S["TOCItem"]),
                Paragraph(t, S["TOCItem"]),
            ]
        )
    t = Table(rows, colWidths=[14 * mm, CONTENT_W - 14 * mm])
    t.setStyle(
        TableStyle(
            [
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("LEFTPADDING", (0, 0), (-1, -1), 2),
                ("RIGHTPADDING", (0, 0), (-1, -1), 2),
                ("TOPPADDING", (0, 0), (-1, -1), 1.5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 1.5),
                ("LINEBELOW", (0, 0), (-1, -2), 0.2, LINE),
            ]
        )
    )
    s.append(t)
    s.append(Spacer(1, 8))

    s.append(p("1. Project at a glance", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(
        kv_table(
            [
                ("Project title", "Portal for Designation of Senior Advocates"),
                ("Sponsoring authority", "High Court of Madras"),
                ("Implementing unit", "Permanent Secretariat, Senior Advocate Designation"),
                ("Legal basis", "Rules for Designation of Senior Advocates, 2026"),
                ("Nature", "Web-based e-governance application (as-built)"),
                ("Primary users", "Advocates (applicants) and High Court staff (admin / reviewer / approver)"),
                ("Core output", "Online Application-cum-Consent Letter (proforma Sl. No. 1-24) with Formats L-1 to L-4"),
                ("Application number", "MHC/SSA/{year}/{serial}  (example: MHC/SSA/2026/0001)"),
                ("Public contact", "hcmadras.tn.gov.in  |  044-2530 1330  |  High Court Buildings, Chennai - 600 104"),
                ("Website", "https://www.hcmadras.tn.gov.in/"),
            ]
        )
    )
    s.append(
        p(
            "This abridged DPR is intended for administrative approval, security review, hosting and "
            "day-to-day operation. It is not a substitute for the Approved Rules or for official notifications "
            "issued by the High Court. In case of any conflict, the Rules and notifications prevail.",
            "Note",
        )
    )

    # 2
    s.append(p("2. Introduction and background", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(
        p(
            "Designation as Senior Advocate is a statutory and institutional process governed by the "
            "Approved Rules of the High Court of Madras. Candidates must furnish a complete "
            "Application-cum-Consent Letter covering personal particulars, enrolment and practice, "
            "reported and unreported judgments, pro bono and amicus work, academic contribution, "
            "declarations and supporting documents."
        )
    )
    s.append(
        p(
            "The Portal for Designation of Senior Advocates is the official online channel for this process. "
            "It replaces unstructured paper collection with a guided, audited workflow: advocates register "
            "with enrolment particulars, complete a seven-step form aligned to the official proforma, "
            "upload prescribed files, receive a unique application number, download a print-ready PDF, "
            "and file a paper book at the Permanent Secretariat. Staff classify applications against each "
            "notified cycle and maintain a full audit trail."
        )
    )

    # 3
    s.append(
        KeepTogether(
            [
                p("3. Need for the portal", "H1"),
                HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6),
                p(
                    "A designation cycle involves a large volume of structured data (Formats L-1 to L-4), "
                    "strict last dates, and scrutiny by the Permanent Secretariat. Manual-only filing creates "
                    "risk of incomplete proforma, ineligible late applications, loss of documents, and weak "
                    "traceability. An online portal is required to:"
                ),
            ]
        )
    )
    need = [
        "Capture every field of the official proforma (Sl. No. 1-24) with draft-save and validation.",
        "Enforce the notified application window and one-application-per-cycle rule.",
        "Accept only prescribed file types and sizes (photograph, signature, enrolment certificate, Formats L-1 to L-4).",
        "Issue a unique application number and a print-ready form for the paper book.",
        "Give staff a single register for listing, wait-listing and deferral, with Excel export and audit logs.",
        "Notify advocates of registration, email verification, password reset and submission by email and SMS.",
        "Publish official notifications and the Rules, 2026 PDF from the same portal.",
    ]
    for item in need:
        s.append(p("&#8226;  " + item, "BulletBody"))
    s.append(Spacer(1, 4))

    # 4
    s.append(p("4. Objectives", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(
        table(
            [
                [cell("No.", head=True), cell("Objective", head=True), cell("Success measure", head=True)],
                [
                    cell("1"),
                    cell("Enable complete online filing of the Application-cum-Consent Letter."),
                    cell("All mandatory fields and uploads captured before submit."),
                ],
                [
                    cell("2"),
                    cell("Align the form with Rules, 2026 and notified cycles."),
                    cell("Windows, age-as-on date and cycle year taken from the official notification."),
                ],
                [
                    cell("3"),
                    cell("Protect applicant data and the integrity of the process."),
                    cell("HTTPS, role-based access, CAPTCHA, lockout, file checks, audit log."),
                ],
                [
                    cell("4"),
                    cell("Support Secretariat review and classification."),
                    cell("Dashboard counts; Select Listed / Wait Listed / Deferred List; PDF and Excel."),
                ],
                [
                    cell("5"),
                    cell("Meet GIGW-oriented publication duties."),
                    cell("Privacy, terms, copyright, hyperlinking, disclaimer and help pages."),
                ],
            ],
            [14 * mm, 78 * mm, CONTENT_W - 92 * mm],
        )
    )
    s.append(Spacer(1, 6))

    # 5
    s.append(p("5. Scope of work", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(p("5.1 In scope", "H2"))
    s.append(
        p(
            "Public pages (login, register, instructions, Rules PDF, notifications); advocate account "
            "(email verification, password reset, account unlock, change password); seven-step "
            "application with Formats L-1 to L-4; secure file store; generated application PDF; "
            "staff dashboard and classification; designation notifications (cycle, dates, document); "
            "master data (qualifications, courts, tribunals, nature of practice, field of law); "
            "email and SMS configuration and templates; audit log; GIGW policy pages."
        )
    )
    s.append(p("5.2 Out of scope", "H2"))
    s.append(
        p(
            "The portal does not decide designation. It does not replace the Permanent Committee / "
            "Full Court process, does not accept the physical paper book, and does not publish a public "
            "list of designated Senior Advocates. Payment gateway, video conferencing, and case-law "
            "search are not part of this system. Eligibility under the Rules remains a matter for the "
            "competent authority, not for automated scoring."
        )
    )
    s.append(p("5.3 Physical filing that remains", "H2"))
    s.append(
        p(
            "After online submission the advocate must still file, at the Permanent Secretariat: "
            "(i) one print-out of the application with attachments as a tagged and indexed paper book; "
            "and (ii) one recent passport-size colour photograph with the name written on the reverse. "
            "Late applications beyond the notified last date are not entertained."
        )
    )

    # 6
    s.append(
        KeepTogether(
            [
                p("6. Users and application process", "H1"),
                HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6),
                p("6.1 Roles", "H2"),
                table(
                    [
                        [cell("Role", head=True), cell("Who", head=True), cell("Principal functions", head=True)],
                        [
                            cell("Applicant"),
                            cell("Advocate enrolled with a Bar Council"),
                            cell("Register, verify email, fill steps 1-7, upload documents, submit, download PDF, track status."),
                        ],
                        [
                            cell("Admin"),
                            cell("Nominated officers of the High Court"),
                            cell("Full staff access: classify applications, notifications, masters, email/SMS, audit, Excel export."),
                        ],
                        [
                            cell("Reviewer / Approver"),
                            cell("Staff roles reserved in the system"),
                            cell("Access to the staff area. Classification is presently restricted to the Admin role."),
                        ],
                    ],
                    [32 * mm, 48 * mm, CONTENT_W - 80 * mm],
                ),
            ]
        )
    )
    s.append(p("6.2 Advocate journey", "H2"))
    s.append(
        table(
            [
                [cell("Step", head=True), cell("Action", head=True), cell("Notes", head=True)],
                [
                    cell("1. Register"),
                    cell("Create account. Name must match the enrolment certificate (no abbreviated name)."),
                    cell("Enrolment lookup against the advocate database; CAPTCHA and rate limit."),
                ],
                [
                    cell("2. Verify"),
                    cell("Confirm email. Unlock account if locked after failed logins."),
                    cell("Single-use links. Password reset valid for one hour."),
                ],
                [
                    cell("3. Instructions"),
                    cell("Read instructions and accept before the form is created."),
                    cell("Application period is taken from the active official notification."),
                ],
                [
                    cell("4. Fill form"),
                    cell("Seven steps; drafts may be saved and resumed."),
                    cell("See section 7.1. One application per designation cycle."),
                ],
                [
                    cell("5. Submit"),
                    cell("Declarations, uploads, final acknowledgement."),
                    cell("Error after submit cannot be rectified by the applicant as of right."),
                ],
                [
                    cell("6. Paper book"),
                    cell("Print PDF and file at the Permanent Secretariat."),
                    cell("Photograph on reverse of physical copy as instructed."),
                ],
                [
                    cell("7. Track"),
                    cell("View status from the advocate dashboard."),
                    cell("Submitted / Select Listed / Wait Listed / Deferred List."),
                ],
            ],
            [28 * mm, 72 * mm, CONTENT_W - 100 * mm],
        )
    )
    s.append(Spacer(1, 4))
    s.append(p("6.3 Status pipeline (staff)", "H2"))
    s.append(
        p(
            "Operational classification used by the Secretariat is: Submitted, Select Listed, "
            "Wait Listed and Deferred List. Draft applications remain with the advocate until submit. "
            "Returned-for-correction is retained in the data model for possible future use. "
            "Status changes are recorded; bulk update is available to Admin."
        )
    )

    # 7
    s.append(
        KeepTogether(
            [
                p("7. Functional modules", "H1"),
                HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6),
                p("7.1 Seven-step application (proforma Sl. No. 1-24)", "H2"),
                table(
                    [
                        [cell("Step", head=True), cell("Title", head=True), cell("Content", head=True)],
                        [cell("1"), cell("Personal details"), cell("Title, full name, date of birth, age (Y/M/D as on notification date), addresses, phone, email, qualifications, age proof and education uploads.")],
                        [cell("2"), cell("Enrolment and practice"), cell("Enrolment number and date, Bar Council, practice period, income, Bar Association, earlier applications to MHC / other courts.")],
                        [cell("3"), cell("Judgments (L-1 / L-2)"), cell("Reported and unreported matters argued as lead / assisting counsel before SC, High Courts and District / Trial Courts, with Format PDFs.")],
                        [cell("4"), cell("Pro bono and academic"), cell("Pro bono (L-3(i)), amicus curiae (L-3(ii)), articles, books, teaching and guest lectures (L-4).")],
                        [cell("5"), cell("Practice domain"), cell("Courts and tribunals practised, nature of practice, field of law, cumulative experience (master lists with 'Others').")],
                        [cell("6"), cell("Declarations"), cell("FIR, criminal cases, Bar Council proceedings, general health, other information, declaration of truth.")],
                        [cell("7"), cell("Uploads and submit"), cell("Photograph, signature, enrolment certificate, Format PDFs; final acknowledgement; application number allotted.")],
                    ],
                    [16 * mm, 42 * mm, CONTENT_W - 58 * mm],
                ),
            ]
        )
    )
    s.append(Spacer(1, 5))
    s.append(
        KeepTogether(
            [
                p("7.2 Document specifications", "H2"),
                table(
                    [
                        [cell("Document", head=True), cell("Format", head=True), cell("Size", head=True)],
                        [cell("Passport photograph"), cell("JPG / JPEG"), cell("20-200 KB")],
                        [cell("Signature"), cell("JPG / JPEG"), cell("20-200 KB")],
                        [cell("Enrolment certificate, age proof, educational qualifications"), cell("PDF"), cell("Up to 5 MB")],
                        [cell("Formats L-1, L-2, L-3(i), L-3(ii), L-4"), cell("PDF"), cell("Up to 5 MB each")],
                    ],
                    [88 * mm, 40 * mm, CONTENT_W - 128 * mm],
                ),
            ]
        )
    )
    s.append(
        p(
            "Images are re-encoded to strip polyglot payloads. PDFs are checked by magic bytes and "
            "scanned for embedded script. Files are stored under writable/uploads (outside the public "
            "web root) and served only to authenticated users with access rights.",
            "Note",
        )
    )
    s.append(p("7.3 Staff modules", "H2"))
    s.append(
        table(
            [
                [cell("Module", head=True), cell("Purpose", head=True)],
                [cell("Dashboard"), cell("Counts of submitted / listed / wait-listed / deferred applications and recent filings.")],
                [cell("Applications"), cell("Search, view full form and attachments, download PDF, classify status, bulk update, Excel export.")],
                [cell("Notifications"), cell("Official designation notifications: number, date, title, PDF, application window and edit window.")],
                [cell("Masters"), cell("Qualifications, courts, tribunals, nature of practice, field of law (with seed defaults).")],
                [cell("Email / SMS settings"), cell("SMTP and SMS gateway configuration with test send.")],
                [cell("Notification templates"), cell("Event templates for registration, verification, reset, submission and related messages.")],
                [cell("Audit"), cell("Immutable-style activity log including failed login attempts.")],
            ],
            [42 * mm, CONTENT_W - 42 * mm],
        )
    )
    s.append(Spacer(1, 5))
    s.append(p("7.4 Public information", "H2"))
    s.append(
        p(
            "Instructions for advocates, the Rules for Designation of Senior Advocates, 2026 (view and "
            "download), published notification PDFs, and GIGW policy pages (privacy, terms, copyright, "
            "hyperlinking, disclaimer, help) are available without login. Last-updated date is displayed "
            "as required for government websites."
        )
    )

    # 8
    s.append(
        KeepTogether(
            [
                p("8. Technical architecture", "H1"),
                HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6),
                p(
                    "The portal is a three-tier web application. The browser talks only to the HTTPS front end. "
                    "The application server (CodeIgniter 4) holds business rules, sessions and file validation. "
                    "PostgreSQL holds structured data. Generated PDFs and uploaded files sit on disk outside "
                    "the document root."
                ),
            ]
        )
    )
    s.append(arch_drawing())
    s.append(p("Figure 1. Logical layers of the portal.", "Caption"))
    s.append(
        KeepTogether(
            [
                table(
                    [
                        [cell("Layer", head=True), cell("Choice", head=True), cell("Remarks", head=True)],
                        [cell("Language / framework"), cell("PHP 8.1.2+  |  CodeIgniter 4"), cell("MVC; PHP 8.2+ recommended.")],
                        [cell("Database"), cell("PostgreSQL"), cell("UTF-8; migrations and seeders under version control.")],
                        [cell("Web / TLS"), cell("Apache or Nginx with HTTPS"), cell("ForceHTTPS filter; HSTS to be enabled at the reverse proxy.")],
                        [cell("PDF"), cell("Dompdf"), cell("Application-cum-Consent Letter print-out.")],
                        [cell("Email"), cell("SMTP (admin-configured)"), cell("Verification, reset, unlock, submission notices.")],
                        [cell("SMS"), cell("HTTP gateway or file log"), cell("Same events; fallback to log if gateway fails.")],
                        [cell("Front end"), cell("HTML, CSS, JavaScript (Bootstrap-based institutional UI)"), cell("Responsive layout; skip-to-content and contrast support.")],
                        [cell("WAF"), cell("ModSecurity with extra SSA rules"), cell("Supplemented by an application input-threat filter.")],
                    ],
                    [38 * mm, 52 * mm, CONTENT_W - 90 * mm],
                ),
            ]
        )
    )
    s.append(p("8.1 Indicative hosting profile", "H2"))
    s.append(
        p(
            "Production should run on the State Data Centre, NIC cloud, or an equivalent government-approved "
            "host with daily backup, TLS certificate, WAF and restricted SSH. Suggested starting size: "
            "2 vCPU, 4-8 GB RAM, 80 GB disk, with growth for uploads during a live cycle. The web root "
            "must point only to the public/ directory. PHP extensions required include intl, mbstring, "
            "json and the PostgreSQL driver. Staging should mirror production for security testing."
        )
    )

    # 9
    s.append(p("9. Security, audit and compliance", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(
        table(
            [
                [cell("Control", head=True), cell("Implementation", head=True)],
                [cell("Transport"), cell("HTTPS enforced at application level; production must terminate TLS with a valid certificate.")],
                [cell("Authentication"), cell("Password accounts; CAPTCHA on login/register; 5 failed attempts per email / 20 per IP in 15 minutes; persisted account lock with email unlock.")],
                [cell("Authorisation"), cell("Role filter on routes (applicant vs admin/reviewer/approver). File URLs are not public.")],
                [cell("Session / CSRF"), cell("Server sessions; CSRF token on all state-changing requests; no-store headers on application forms.")],
                [cell("Input"), cell("Parameterised queries; InvalidChars; application filter for PHP tags and PostgreSQL injection patterns.")],
                [cell("Uploads"), cell("Type, MIME, size, magic-byte and script checks; JPEG re-encode; storage outside web root.")],
                [cell("Audit"), cell("Status history and audit_logs including failed logins; notification_logs for email/SMS.")],
                [cell("Privacy"), cell("Data used only for designation processing. Not sold. Access limited by role.")],
                [cell("GIGW"), cell("Policy pages, last-updated date, organisational footer, content ownership line.")],
            ],
            [32 * mm, CONTENT_W - 32 * mm],
        )
    )
    s.append(
        p(
            "Before public go-live, a vulnerability assessment and penetration test (VAPT) by a CERT-In "
            "empanelled auditor should be completed and high/critical findings closed. Periodic re-audit "
            "should follow the High Court's IT security calendar. Security incidents are to be reported "
            "to the Registrar (IT-cum-Statistics) and handled per CERT-In directions.",
            "Note",
        )
    )

    # 10
    s.append(p("10. Implementation, hosting and support", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(p("10.1 As-built status", "H2"))
    s.append(
        p(
            "The software described in this DPR is already developed and in use as an internal / "
            "pre-production portal (registration, multi-step application, staff classification, "
            "notifications, masters, email/SMS, audit). Remaining institutional tasks are hosting "
            "hardening, VAPT, production DNS and TLS, SMS gateway credentials, advocate-database "
            "load, staff training and issue of the live notification."
        )
    )
    s.append(
        KeepTogether(
            [
                p("10.2 Suggested go-live sequence", "H2"),
                table(
                    [
                        [cell("Phase", head=True), cell("Activity", head=True), cell("Owner", head=True)],
                        [cell("A"), cell("Production host, TLS, WAF, backups, DNS."), cell("IT wing / SDC")],
                        [cell("B"), cell("Load advocate enrolment data; create staff users; SMTP and SMS live test."), cell("Permanent Secretariat + IT")],
                        [cell("C"), cell("VAPT and closure of findings; GIGW content sign-off."), cell("IT + Registrar (IT-cum-Statistics)")],
                        [cell("D"), cell("Staff rehearsal on a closed cycle; print-path check of PDF and paper book."), cell("Permanent Secretariat")],
                        [cell("E"), cell("Publish official notification; open application window; helpdesk during the cycle."), cell("High Court")],
                        [cell("F"), cell("After last date: freeze window, classify, archive, AMC."), cell("Permanent Secretariat + IT")],
                    ],
                    [18 * mm, 108 * mm, CONTENT_W - 126 * mm],
                ),
            ]
        )
    )
    s.append(p("10.3 Training and helpdesk", "H2"))
    s.append(
        p(
            "One half-day session for Secretariat staff (dashboard, search, PDF, status, notifications, "
            "Excel) and a short advocate FAQ on the Instructions page are sufficient. Help during the "
            "open window should be through the published email and 044-2530 1330, with official "
            "correspondence to the Registrar (Administration) where required."
        )
    )
    s.append(p("10.4 Backup and continuity", "H2"))
    s.append(
        p(
            "Database and upload directory are to be backed up daily during an open cycle and retained "
            "per judicial record-retention norms. In a prolonged outage, the competent authority may "
            "notify an alternate channel (including physical filing at the Permanent Secretariat). "
            "The official Rules and notifications remain the source of truth if the portal is unavailable."
        )
    )

    # 11
    s.append(p("11. Indicative cost and schedule", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(
        p(
            "Figures below are indicative for administrative planning. They do not constitute a "
            "quotation. Actual cost will follow NIC / SDC / GEM rates, existing High Court licences, "
            "and the outcome of VAPT. Application development is treated as completed (as-built)."
        )
    )
    s.append(
        table(
            [
                [cell("Cost head", head=True), cell("Basis", head=True), cell("Indicative (Rs. lakh)", head=True)],
                [cell("Application software (as-built)"), cell("Already developed; no fresh build cost in this DPR"), cell("Nil (sunk)")],
                [cell("Production hosting (12 months)"), cell("SDC / NIC / approved cloud, TLS, backups"), cell("1.50 - 3.00")],
                [cell("VAPT (CERT-In empanelled)"), cell("One cycle before go-live; re-test of fixes"), cell("1.00 - 2.00")],
                [cell("SMS transactional pack"), cell("As per NIC / TRAI-compliant gateway"), cell("0.20 - 0.50")],
                [cell("Training, FAQ, go-live support"), cell("Staff rehearsal + helpdesk during first cycle"), cell("0.25 - 0.50")],
                [cell("Annual maintenance (Year 1)"), cell("Bug-fix, cycle configuration, minor change requests (~15-20%)"), cell("1.50 - 2.50")],
                [cell("Contingency (10%)"), cell("Unforeseen hosting or audit work"), cell("0.45 - 0.85")],
                [
                    cell("<b>Indicative Year-1 total</b>"),
                    cell("Excluding sunk development"),
                    cell("<b>4.90 - 9.35</b>"),
                ],
            ],
            [52 * mm, 78 * mm, CONTENT_W - 130 * mm],
        )
    )
    s.append(
        p(
            "Schedule: Phases A-C can run in parallel over 3-4 weeks after host allocation. Phase D "
            "(rehearsal) needs about one week. Phase E is gated by the official notification. A "
            "designation cycle typically remains open only for the window printed on that notification.",
            "Note",
        )
    )

    # 12
    s.append(p("12. Benefits, risks and recommendation", "H1"))
    s.append(
        HRFlowable(width="100%", thickness=0.6, color=BRASS, spaceAfter=6)
    )
    s.append(
        KeepTogether(
            [
                p("12.1 Benefits", "H2"),
                table(
                    [
                        [cell("Stakeholder", head=True), cell("Benefit", head=True)],
                        [cell("Advocates"), cell("Guided form, draft save, clear document rules, unique number, printable PDF, status visibility.")],
                        [cell("Permanent Secretariat"), cell("Complete digital register, cycle-wise window, bulk classification, Excel, audit trail.")],
                        [cell("High Court"), cell("Process aligned to Rules, 2026; reduced incomplete filings; GIGW-oriented public pages.")],
                        [cell("Record-keeping"), cell("Application PDF, uploads and status history retained under institutional control.")],
                    ],
                    [42 * mm, CONTENT_W - 42 * mm],
                ),
            ]
        )
    )
    s.append(
        KeepTogether(
            [
                p("12.2 Principal risks and mitigation", "H2"),
                table(
                    [
                        [cell("Risk", head=True), cell("Mitigation", head=True)],
                        [cell("Rush of filings near the last date"), cell("Capacity test; applicants advised to submit early; window includes date and time.")],
                        [cell("Incorrect or incomplete data"), cell("Step validation; instruction acknowledgement; paper-book scrutiny at Secretariat.")],
                        [cell("Malicious upload or injection"), cell("File re-encode and PDF checks; WAF; input-threat filter; parameterised SQL.")],
                        [cell("Account takeover"), cell("CAPTCHA, lockout, email unlock, short-lived reset tokens.")],
                        [cell("Hosting outage during the window"), cell("Backups; SDC SLA; authority may notify an alternate filing channel.")],
                        [cell("Conflict with Rules / notification"), cell("Portal content yields to the official Rules and High Court notifications.")],
                    ],
                    [52 * mm, CONTENT_W - 52 * mm],
                ),
            ]
        )
    )
    s.append(p("12.3 Recommendation", "H2"))
    s.append(
        p(
            "It is recommended that the High Court of Madras adopt this as-built portal as the official "
            "online channel for the Designation of Senior Advocates, subject to: (i) production hosting "
            "on an approved government facility with TLS and backup; (ii) closure of VAPT findings; "
            "(iii) live SMTP and SMS; (iv) loaded enrolment master; and (v) issue of the designation "
            "notification that opens the application window. The Permanent Secretariat should continue "
            "to receive the physical paper book as prescribed in the instructions."
        )
    )
    s.append(
        KeepTogether(
            [
                Spacer(1, 8),
                HRFlowable(width="100%", thickness=0.6, color=NAVY, spaceAfter=8),
                p("Document control", "H2"),
                kv_table(
                    [
                        ("Title", "Detailed Project Report -- Portal for Designation of Senior Advocates"),
                        ("Version", "1.0 (abridged, under 10 pages)"),
                        ("Date", "August 2026"),
                        ("Prepared for", "High Court of Madras -- Permanent Secretariat"),
                        ("Web Information Manager", "The Registrar (IT-cum-Statistics)"),
                        ("Next review", "After first live designation cycle, or on amendment of the Rules"),
                    ]
                ),
            ]
        )
    )
    return s


def main():
    prepare_seal()
    doc = BaseDocTemplate(
        OUT,
        pagesize=A4,
        title="Detailed Project Report -- Portal for Designation of Senior Advocates",
        author="High Court of Madras",
        subject="Abridged DPR for the Senior Advocate Designation Portal",
    )
    cover_frame = Frame(0, 0, PAGE_W, PAGE_H, id="cover", leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0)
    body_frame = Frame(LEFT, BOTTOM, CONTENT_W, PAGE_H - TOP - BOTTOM, id="body")
    doc.addPageTemplates(
        [
            PageTemplate(id="cover", frames=cover_frame, onPage=cover_page),
            PageTemplate(id="body", frames=body_frame, onPage=header_footer),
        ]
    )
    doc.build(story())
    print("Wrote", OUT)


if __name__ == "__main__":
    main()
