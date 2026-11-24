# Diario template contract

Reference: D:/xampp/htdocs/guagua/comun/doc/Plantilla guía.docx
SHA256: dd0c6f549cefa191144fe7c1ba7855b26f3faa4af6118f480e5b3da987f6e8a4
Reference has one page and one section, four empty body paragraphs.
Rendered with Microsoft Word read-only to tmp/diario-template.pdf because the
packaged render_docx.py has no LibreOffice on this Windows host. Reference image:
tmp/diario-template-page-1.png. All page content was inspected.

Letter page: 12240 x 15840 twips. Margins top 2552, right 1134, bottom 1418,
left 1701; header 340, footer 0. No titlePg flag. Existing section XML must be
copied unchanged. Header/footer references and all relationships stay intact.
word/header2.xml carries the page-size institutional background image, behind
text; word/media/image1.png is preserve-only. It includes both letterhead and
footer artwork and appears on every page. Other headers/footers are blank.

Source body uses Times New Roman and 276 line spacing, with bold in its first
blank paragraphs. Default size is 24 half points; after spacing 160. Reuse this
body rhythm; body text is 12 pt. Cover uses source Title style plus explicit
black Times New Roman, 26 pt title, 16 pt subtitles, 18 pt month/year; centered.
Dates use 14 pt bold, titles 13 pt bold; keep with next. Reflections italic,
with a bold lead-in. Headings black. Page-break-before starts every daily entry.

Edit slot: replace only children of word/document.xml/w:document/w:body before
w:sectPr, excluding preserved section. The source has no «fecha» token or cover
text. User explicitly approved preserving cover with current month/year. Add
the cover text and month/year (America/Bogota) within the existing page system.
Insert the diary on page two. No source body prose must be retained.

Content authority: user PDF Diary August, 11 pages, one cover and ten entries.
Each entry contains date, short descriptive title, chronological first-person
pedagogical narrative, and Reflexión del día. This is a format reference, not
facts to insert in new entries. Entries may flow onto additional pages if needed.
No tables, fields, footnotes, content controls or body images are authored.

Package preservation: tmp/diario-package-inventory.json inventories every source
part. All parts except word/document.xml must match byte-for-byte. Source file
hash stays unchanged. Render final test cover and all final test pages; check
page two begins with the first date, cover shows Octubre de 2026, no overlap
with background, clipping or unexpected blank pages.
