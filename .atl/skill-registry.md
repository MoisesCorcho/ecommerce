# Skill Registry — ecommerce

Index of available skills and project convention/steering files. Subagents receive
exact paths and read the full `SKILL.md` source of truth — this is an index, not a
summary. Deduplicated by skill name; project-level skills preferred over user-level.
SDD skills (`sdd-*`), `_shared`, and `skill-registry` are intentionally excluded.

**Project**: ecommerce (Laravel 13 / Livewire v4 / Filament v5 / Tailwind v4 / PHPUnit 12 / Sail)
**Generated**: 2026-07-22 · **Persistence**: engram

---

## Project-level skills

| Skill | Trigger | Scope | Path |
|-------|---------|-------|------|
| filament-admin-standards | Filament v5 admin Resources/Pages/Widgets/forms/tables/filters/actions, panel UX, branding | project (admin UI) | `.claude/skills/filament-admin-standards/SKILL.md` |
| laravel-best-practices | Writing/reviewing/refactoring Laravel PHP: controllers, models, migrations, form requests, policies, jobs, Eloquent, N+1, caching, authz, validation, queues | project (backend) | `.claude/skills/laravel-best-practices/SKILL.md` |
| tailwindcss-development | Tailwind utility classes in templates (Blade/JSX/Vue): responsive grids, flex/grid layouts, components, dark mode, spacing/typography, v3/v4 | project (frontend styling) | `.agents/skills/tailwindcss-development/SKILL.md` |

> `filament-admin-standards` and `laravel-best-practices` also exist under
> `.agents/skills/`; `tailwindcss-development` also exists under `.claude/skills/`.
> Deduplicated — first listed path is canonical.

---

## User-level skills

| Skill | Trigger | Scope | Path |
|-------|---------|-------|------|
| branch-pr | Creating, opening, or preparing PRs for review (Gentle AI issue-first checks) | workflow | `/home/noro/.opencode/skills/branch-pr/SKILL.md` |
| chained-pr | PRs over 400 lines, stacked PRs, review slices — split oversized changes into chained PRs | workflow | `/home/noro/.opencode/skills/chained-pr/SKILL.md` |
| cognitive-doc-design | Writing guides, READMEs, RFCs, onboarding, architecture, review-facing docs | docs | `/home/noro/.opencode/skills/cognitive-doc-design/SKILL.md` |
| comment-writer | PR feedback, issue replies, reviews, Slack/GitHub comments — warm direct collaboration | comms | `/home/noro/.opencode/skills/comment-writer/SKILL.md` |
| customize-opencode | Editing opencode config (opencode.json, .opencode/, ~/.config/opencode/), agents, subagents, skills, plugins, MCP servers, permission rules | tooling | built-in (no file path) |
| find-skills | "how do I do X", "find a skill for X", discovering/installing agent skills | meta | `/home/noro/.agents/skills/find-skills/SKILL.md` |
| go-testing | Go tests, coverage, Bubbletea teatest, golden files | testing (Go) | `/home/noro/.opencode/skills/go-testing/SKILL.md` |
| issue-creation | Creating GitHub issues, bug reports, feature requests (Gentle AI issue-first) | workflow | `/home/noro/.opencode/skills/issue-creation/SKILL.md` |
| judgment-day | Judgment day, dual/adversarial review, juzgar — blind dual review with ≤2 fix rounds | review | `/home/noro/.opencode/skills/judgment-day/SKILL.md` |
| skill-creator | New skills, agent instructions, documenting AI usage patterns — LLM-first skills with frontmatter | meta | `/home/noro/.opencode/skills/skill-creator/SKILL.md` |
| skill-improver | Improve/audit/refactor existing skills, skill quality | meta | `/home/noro/.opencode/skills/skill-improver/SKILL.md` |
| work-unit-commits | Plan commits as reviewable work units; commit splitting, chained PRs, keeping tests/docs with code | workflow | `/home/noro/.opencode/skills/work-unit-commits/SKILL.md` |

---

## Project conventions & steering (index files)

These are not skills but authoritative convention/steering sources referenced by
`AGENTS.md` / `CLAUDE.md`. Include both the index and the referenced files.

| File | Role | Path |
|------|------|------|
| AGENTS.md | Top-level convention index (mirrors `.ai/project-conventions` + Boost + Sail + Pint + PHPUnit + Filament rules) | `AGENTS.md` |
| CLAUDE.md | Same convention index (agent-facing mirror of AGENTS.md) | `CLAUDE.md` |
| 00-how-to-use.md | How to work with SDD in this repo; canonical source map; state convention | `specs/_global/00-how-to-use.md` |
| 01-product-and-roadmap.md | Product vision, feature order (F01…F08), dependencies, no-goals | `specs/_global/01-product-and-roadmap.md` |
| 02-feature-quality.md | EARS criteria, R-ids, tasks traceability, audit & correction protocol | `specs/_global/02-feature-quality.md` |

---

## SDD pipeline skills (excluded from index — invoked by orchestrator)

`sdd-init`, `sdd-explore`, `sdd-propose`, `sdd-spec`, `sdd-design`, `sdd-tasks`,
`sdd-apply`, `sdd-verify`, `sdd-archive`, `sdd-onboard`, `_shared`, `skill-registry`.
