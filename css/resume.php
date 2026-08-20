```css
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    background: #eef1f5;
    font-family: Arial, Helvetica, sans-serif;
    color: #222;
}


/* =========================================
   TOOLBAR
========================================= */

.resume-toolbar {
    width: 100%;
    padding: 15px 25px;
    background: #ffffff;
    border-bottom: 1px solid #ddd;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.back-btn,
.print-btn {
    padding: 10px 18px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
    cursor: pointer;
}

.back-btn {
    background: #f1f1f1;
    color: #222;
}

.print-btn {
    border: none;
    background: #1f4e79;
    color: #fff;
}


/* =========================================
   A4 RESUME
========================================= */

.resume-page {
    width: 210mm;
    min-height: 297mm;

    margin: 30px auto;
    padding: 22mm 20mm;

    background: #ffffff;

    box-shadow:
        0 4px 20px rgba(0, 0, 0, 0.12);
}


/* =========================================
   HEADER
========================================= */

.resume-header {
    display: flex;
    align-items: center;

    gap: 25px;

    padding-bottom: 20px;

    border-bottom: 2px solid #1f4e79;
}

.profile-area {
    flex-shrink: 0;
}

.profile-photo,
.profile-placeholder {
    width: 115px;
    height: 115px;

    border-radius: 50%;
}

.profile-photo {
    object-fit: cover;

    border: 4px solid #1f4e79;
}

.profile-placeholder {
    display: flex;
    justify-content: center;
    align-items: center;

    background: #1f4e79;

    color: #ffffff;

    font-size: 44px;
    font-weight: bold;
}

.header-details {
    flex: 1;
}

.header-details h1 {
    margin: 0 0 6px;

    color: #1f4e79;

    font-size: 32px;
    font-weight: 700;
}

.header-details h2 {
    margin: 0 0 12px;

    color: #555;

    font-size: 16px;
    font-weight: normal;
}

.contact-line {
    display: flex;
    flex-wrap: wrap;

    gap: 12px;

    margin-top: 6px;

    font-size: 13px;
}

.contact-line a {
    color: #1f4e79;
    text-decoration: none;
}


/* =========================================
   SECTIONS
========================================= */

.resume-section {
    margin-top: 25px;
}

.resume-section h3 {
    margin: 0 0 13px;

    padding-bottom: 7px;

    border-bottom: 1px solid #d9d9d9;

    color: #1f4e79;

    font-size: 17px;

    letter-spacing: 0.5px;
}

.resume-section p {
    margin: 5px 0;

    font-size: 14px;

    line-height: 1.6;
}


/* =========================================
   EDUCATION
========================================= */

.education-item {
    margin-bottom: 18px;
}

.education-top {
    display: flex;

    justify-content: space-between;

    gap: 20px;
}

.education-top h4 {
    margin: 0 0 5px;

    font-size: 15px;
}

.institution {
    font-size: 14px;
    font-weight: 600;
}

.small-text {
    margin-top: 4px;

    color: #666;

    font-size: 13px;
}

.year {
    color: #555;

    font-size: 13px;

    white-space: nowrap;
}

.education-result {
    display: flex;

    gap: 20px;

    margin-top: 7px;

    color: #555;

    font-size: 13px;
}


/* =========================================
   SKILLS
========================================= */

.skills-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 15px 25px;
}

.skill-item {
    width: 100%;
}

.skill-heading {
    display: flex;

    justify-content: space-between;

    margin-bottom: 6px;

    font-size: 13px;
}

.skill-heading span {
    color: #666;
}

.skill-bar {
    width: 100%;

    height: 6px;

    background: #e5e5e5;

    border-radius: 10px;

    overflow: hidden;
}

.skill-progress {
    height: 100%;

    background: #1f4e79;

    border-radius: 10px;
}


/* =========================================
   PROJECTS
========================================= */

.project-item {
    margin-bottom: 18px;
}

.project-item h4 {
    margin: 0 0 5px;

    font-size: 15px;
}

.technologies {
    margin: 3px 0 7px;

    color: #1f4e79;

    font-size: 12px;

    font-weight: bold;
}

.project-link,
.certificate-link {
    display: inline-block;

    margin-top: 5px;

    color: #1f4e79;

    font-size: 13px;

    font-weight: bold;

    text-decoration: none;
}


/* =========================================
   CONTACT
========================================= */

.contact-details p {
    font-size: 13px;
}


/* =========================================
   CERTIFICATE
========================================= */

.certificate-item {
    margin-bottom: 15px;
}

.certificate-item h4 {
    margin: 0 0 5px;

    font-size: 15px;
}

.certificate-item p {
    color: #555;

    font-size: 13px;
}


/* =========================================
   MOBILE
========================================= */

@media screen and (max-width: 800px) {

    .resume-toolbar {
        padding: 12px;
    }

    .resume-page {
        width: 95%;

        min-height: auto;

        margin: 15px auto;

        padding: 25px;
    }

    .resume-header {
        flex-direction: column;

        text-align: center;
    }

    .contact-line {
        justify-content: center;
    }

    .education-top {
        flex-direction: column;

        gap: 5px;
    }

    .skills-grid {
        grid-template-columns: 1fr;
    }
}


/* =========================================
   PRINT / PDF
========================================= */

@media print {

    body {
        background: #ffffff;
    }

    .resume-toolbar {
        display: none;
    }

    .resume-page {
        width: 210mm;
        min-height: 297mm;

        margin: 0;

        padding: 18mm;

        box-shadow: none;
    }

    @page {
        size: A4;
        margin: 0;
    }

    .resume-section,
    .education-item,
    .project-item,
    .certificate-item {
        break-inside: avoid;
    }
}
```
