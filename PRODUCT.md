# Product

<!-- impeccable:product-schema 1 -->

Scope: the **NCF 17 event microsite** (`ncf17-web`) only. The sibling
`doingbusiness-web`, `doingbusiness-admin` and `doingbusiness-backend` services
are deliberately not covered here — confirmed with the user at init.

## Platform

web

## Users

**Invited delegates** — the people already invited to or expected at the 17th
National Competitiveness Forum: senior government (MoFPED, MAAIF, MTIC, MoLG,
NPA), commercial and development banks, business associations, cooperatives,
development partners and academia.

They arrive already knowing they are going. The job is orientation, not
persuasion: what is the theme, what happens when, who is speaking, where is it,
and what should I read first. Most will check the page in short visits ahead of
the day, and at least some on a phone.

Three other audiences were offered at init and **not** confirmed: private sector
firms wanting to get in, press and the public record, and PSDU's own internal
briefing use. Treat them as unconfirmed rather than excluded — do not design
primarily for them without asking.

## Product Purpose

A single public page that carries the 17th National Competitiveness Forum:
its theme, background, agenda, speakers and papers, plus a live countdown to
the day.

The Forum itself is the public–private dialogue platform of the Directorate of
Economic Affairs, MoFPED, convened annually by the Private Sector Development
Unit. Its conclusions feed the national annual budget process. The site is the
Forum's shareable public face in the weeks before it convenes.

Success is a delegate arriving on the day already knowing the theme, the running
order and which session concerns them — and knowing the Doing Business Portal
exists.

## Positioning

Most event microsites can only describe an event. This one sits on top of a
programme with a public record: every action agreed at a Forum is tracked
individually, with a named lead institution and a status, on the Doing Business
Portal's Reforms Tracker.

So the site can do what a neighbouring event page cannot truthfully do — show
what the *last* Forum produced, and point at the register where this one's
outcomes will land. The 16th Forum's 18 agreed actions across 12 issue areas are
already published there.

## Operating Context

- **The event:** Friday 25 September 2026, 8:00 am – 5:00 pm EAT, Kampala
  Serena Hotel, Kampala. In person. One day, three working sessions plus an
  opening session.
- **Convened by:** Private Sector Development Unit, Directorate of Economic
  Affairs, Ministry of Finance, Planning and Economic Development.
- **Source of truth:** a **draft** concept note dated 22 July 2026, held by PSDU
  and not yet cleared for publication
  (`~/Downloads/Draft 17th NCF Concept Note funds to firms 20260722.docx`).
  The programme comes from its Annex 1; the archive of past editions from its
  Annex II.
- **Reading situation:** short visits in the weeks before the Forum, frequently
  on a phone, to check one thing — a time, a name, a venue.
- **Neighbouring surfaces:** the Doing Business Portal at
  `doingbusiness.fiika.dev` carries the Reforms Tracker, the full forum archive
  and the issues helpdesk. The microsite links out to all three.

## Capabilities and Constraints

**Confirmed**

- One page, five anchored sections: home, about, agenda, speakers, resources.
- The landing page is fully static and client-rendered (`ssr: false`,
  `nuxt generate`). Its own content never needs a backend or a runtime fetch —
  the page must survive a traffic spike on the day with nothing else running.
- All landing-page content lives in `app/data/forum.ts`. Nothing is fetched.
- The `#resources` section hands off to the PHP file repository in
  `repository/`, deployed beneath the static bundle at `/repository`. That half
  does have a database; the landing page still does not.
- Deployed as one FTP upload to the PHP host: static bundle at the web root,
  `repository/` beneath it. Both halves share an origin.
- Light mode only. The theme is `doingbusiness-web`'s token set.
- Outbound links resolve from a single `EVENT.portalUrl`.

**Draft-status honesty (a fact of the source, not an invented rule)**

The concept note is explicitly a draft. Seven of the twelve speaker entries are
placeholders: the note names the office ("Key note address by Hon. MFPED",
"the presenter – DCO/Mubende District") but not the individual. Those entries
carry `provisional: true` and render a visible "To be confirmed" badge, and the
agenda carries a draft notice. Future work must not quietly promote unconfirmed
detail to settled fact.

**Open / undecided — record, do not invent**

- **Date discrepancy in the source.** The concept note body says 25 September
  2026; its Annex 1 header reads "25rd September, 2025". The site uses 2026,
  matching the Portal's own archive record. PSDU to confirm.
- **Concept note publication.** The PDF is not yet cleared; the resource card
  says so rather than linking to nothing.
- **Registration.** There is no registration mechanism. A form existed briefly
  and was removed with the multi-page version; no collection service has been
  chosen.
- **Final domain.** Not yet decided.

## Brand Commitments

In use in the implementation, though the user did not mark them binding at init:

- The Uganda coat of arms and the MoFPED ministry badge. The badge is the header
  mark; both appear in the footer.
- Ministry naming is spelled in full — "Ministry of Finance, Planning and
  Economic Development", "Private Sector Development Unit".
- The Forum theme is quoted verbatim from the concept note: *"From Funds to
  Firms: Turning GoU Wealth Creation Initiatives into Competitive Enterprises
  for Uganda's US$500 Billion Economy."*
- The Forum has no sub-brand of its own and should not acquire one.

## Evidence on Hand

**Real and available**

- The draft concept note (theme, background, justification, objectives, Annex 1
  programme, Annex II archive).
- 16th NCF outcome matrix, published January 2026 —
  `public/docs/ncf16-outcome-matrix.pdf`.
- Forum photography from the 16th, 15th and 12th editions, credited to MoFPED —
  `public/img/forum/`. Sourced from `doingbusiness-backend/seed/event_photos/`.
- The Portal's own forum archive and reform registers
  (`doingbusiness-backend/seed/events_archive.json`, `reforms_ncf16.json`),
  from which the past-editions content and the nine carried-forward priorities
  were taken.
- Sourced but currently unrendered content is kept in `app/data/forum.ts`:
  `BACKGROUND`, `CHALLENGES`, `SOLUTION`, `EXPECTED_OUTCOMES`, `ARCHIVE`,
  `LAST_FORUM_PRIORITIES`, `PARTICIPATING`, `GALLERY`.

**Absences future work must not fabricate**

- No speaker portraits. Cards use monograms.
- No attendance figures, delegate counts or exhibitor list for the 17th Forum.
- No sponsors or partners — the institutions listed are presenters and
  discussants named in the concept note, nothing more.
- No testimonials, quotes or press coverage.
- No outcomes for the 17th Forum. It has not happened.

## Product Principles

1. **Every section answers "what do I do next?"** — inherited from the
   programme's locked framing. Telling a delegate something exists is not the
   same as helping them act on it.
2. **Unconfirmed stays visibly unconfirmed.** The draft badge is a feature, not
   an apology. A government forum loses more from a wrong name than from an
   empty one.
3. **The Forum's authority is its record.** Where the page can point at what a
   past Forum actually changed, it should.
4. **Complete on a phone.** A delegate checking one fact on a handset is the
   design case; nothing may be desktop-only or require sideways scrolling.
5. **Nothing depends on a service being up.** Static by construction, so the
   page is at its most reliable on the day it matters most.

## Accessibility & Inclusion

**WCAG 2.1 AA is required**, not best-effort — confirmed at init. Contrast,
full keyboard operation and correct screen-reader semantics are product
requirements. This is a government service surface and is held to the standard
one.
