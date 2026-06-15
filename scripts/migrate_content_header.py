#!/usr/bin/env python3
"""Replace legacy <section class="content-header"> blocks with components/page_header."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1] / "app" / "Views"
SKIP = {
    "components/page_header.php",
    "layouts/admin_template.php",
}

SECTION_RE = re.compile(
    r"<section\s+class=\"content-header\">(.*?)</section>",
    re.DOTALL | re.IGNORECASE,
)
H1_RE = re.compile(r"<h1[^>]*>(.*?)</h1>", re.DOTALL | re.IGNORECASE)
ICON_RE = re.compile(r"<i\s+class=\"([^\"]+)\"", re.IGNORECASE)
ACTIVE_CRUMB_RE = re.compile(
    r"<li\s+class=\"breadcrumb-item\s+active\"[^>]*>(.*?)</li>",
    re.DOTALL | re.IGNORECASE,
)
DASH_URL_RE = re.compile(
    r"<li\s+class=\"breadcrumb-item\"[^>]*>\s*<a[^>]+href=\"([^\"]+)\"",
    re.IGNORECASE,
)
TAG_RE = re.compile(r"<[^>]+>")


def strip_tags(s: str) -> str:
    return re.sub(r"\s+", " ", TAG_RE.sub("", s)).strip()


def build_page_header(title: str, active: str, dash_url: str | None, icon: str | None) -> str:
    url = dash_url or "<?= base_url('admin/dashboard') ?>"
    if not url.startswith("<?") and not url.startswith("http"):
        url = f"<?= base_url('{url.lstrip('/')}') ?>" if url.startswith("admin/") else f"<?= esc('{url}') ?>"

    lines = ["<?= view('components/page_header', ["]
    lines.append(f"    'title' => {php_str(title)},")
    if icon:
        lines.append(f"    'icon' => {php_str(icon)},")
    lines.append("    'breadcrumbs' => [")
    lines.append(f"        ['label' => 'Dashboard', 'url' => {url}],")
    lines.append(f"        ['label' => {php_str(active)}, 'active' => true],")
    lines.append("    ],")
    lines.append("]) ?>")
    return "\n".join(lines)


def php_str(s: str) -> str:
    s = s.replace("\\", "\\\\").replace("'", "\\'")
    return f"'{s}'"


def needs_datatables(content: str) -> bool:
    if "uiNeedsDataTables" in content[:800]:
        return False
    return bool(
        re.search(r"DataTable\s*\(", content)
        or re.search(r'id="[^"]*datatable', content, re.I)
        or "dataTable(" in content
    )


def migrate_file(path: Path) -> bool:
    rel = path.relative_to(ROOT).as_posix()
    if rel in SKIP or "sms-page-header" in path.read_text(encoding="utf-8", errors="ignore"):
        # still migrate if legacy section without sms-page-header exists
        pass

    text = path.read_text(encoding="utf-8", errors="ignore")
    if "sms-page-header" in text and "<section class=\"content-header\">" not in text.replace(
        "sms-page-header", ""
    ):
        return False

    changed = False

    def replacer(m: re.Match[str]) -> str:
        nonlocal changed
        block = m.group(0)
        if "sms-page-header" in block:
            return block
        inner = m.group(1)
        h1 = H1_RE.search(inner)
        if not h1:
            return block
        title_raw = h1.group(1)
        icon_m = ICON_RE.search(title_raw)
        icon = icon_m.group(1) if icon_m else None
        title = strip_tags(title_raw)
        if not title:
            return block
        active_m = ACTIVE_CRUMB_RE.search(inner)
        active = strip_tags(active_m.group(1)) if active_m else title
        dash_m = DASH_URL_RE.search(inner)
        dash_url = dash_m.group(1) if dash_m else None
        # Skip blocks that look like action toolbar (buttons, no breadcrumb)
        if "breadcrumb" not in inner.lower() and "btn" in inner.lower():
            return block
        changed = True
        return build_page_header(title, active, dash_url, icon) + "\n"

    new_text = SECTION_RE.sub(replacer, text)

    if not changed:
        return False

    if needs_datatables(new_text) and "<?php" in new_text[:5]:
        if not new_text.lstrip().startswith("<?php $uiNeedsDataTables"):
            new_text = "<?php $uiNeedsDataTables = true; ?>\n" + new_text
    elif needs_datatables(new_text) and new_text.lstrip().startswith("<?="):
        new_text = "<?php $uiNeedsDataTables = true; ?>\n" + new_text

    path.write_text(new_text, encoding="utf-8")
    return True


def main() -> None:
    migrated = []
    for path in sorted(ROOT.rglob("*.php")):
        rel = path.relative_to(ROOT).as_posix()
        if rel in SKIP:
            continue
        raw = path.read_text(encoding="utf-8", errors="ignore")
        if "<section class=\"content-header\">" not in raw:
            continue
        if migrate_file(path):
            migrated.append(rel)
    print(f"Migrated {len(migrated)} files:")
    for r in migrated:
        print(f"  {r}")


if __name__ == "__main__":
    main()
