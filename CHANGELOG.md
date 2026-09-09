# Changelog

All notable changes to `laravel-query-builder-powered` will be documented in this file.

## Unreleased

### Fixed — a term is matched as a literal (**behavioural**)

`GlobalFilter` and `TextFilter` pasted the caller's term into the `LIKE` binding with the two
characters the comparison reserves still live: `_` matched any single character and `%` any run of
them. A term carrying either searched for more than it said — `k_w@example.com` also answered with
`k.w@example.com` and with every `kXw@…`, and `%` on its own answered with the whole table. Both now
escape `%`, `_` and the escape character itself before the term becomes a binding.

`$eq` and `$notEq` compare the **whole value** as a result, which is what their name promises. They
were compiled as a `LIKE` with the bare value, so an underscore in the value made them answer rows
that are not equal to what was asked for. The Mongo filters have always anchored `$eq` (`^…$` over
`preg_quote`) and have never had the defect; this is the SQL half catching up.

`$in` and `$notIn` are untouched — they are a `whereIn` and already compare exactly, and escaping
them would send the backslashes to the database as part of the value.

The surrounding `%` of `$contains` and of the global filter stays: what it makes is a substring
search, and that is the filter's own doing rather than the caller's term.

Escaping relies on the backslash being `LIKE`'s default escape character, which holds on MySQL and
PostgreSQL. No `ESCAPE` clause is emitted: the clause takes a string literal that the two engines
spell differently, so saying it is less portable than not saying it.

Found from teamq-ec/rudy-api-V2.0#450.

## v4.0.0 - 2026-07-24

### What's Changed

Support for **spatie/laravel-query-builder ^7** — the constraint now allows `^6.0|^7.0`, unblocking projects already on v7 (RUDY V2 backend).

- Production code (`src/`) unchanged: the `Filter`/`Sort` interfaces already declare the `: void` return type v7 requires.
- Test allow-lists migrated to the spread operator (`allowedFilters(...[...])`), compatible with both v6 and v7.
- Green across the matrix: PHP 8.4/8.5 × Laravel 12/13.

PRs: #43 (query-builder ^7 support).

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/3.2.0...4.0.0

## v3.2.0 - 2026-05-11

### What's Changed

* feat(filters): add MongoDB filter set (SBS-966) by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/41
* Bump dependabot/fetch-metadata from 2.5.0 to 3.1.0 by @dependabot[bot] in https://github.com/teamq-ec/teamq-laravel-datatables/pull/40

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/3.1.2...3.2.0

## v3.1.2 - 2026-03-04

### What's Changed

* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/teamq-ec/teamq-laravel-datatables/pull/36
* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/teamq-ec/teamq-laravel-datatables/pull/37
* Remove unused script and update parameter references by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/38

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/3.1.1...3.1.2

## 3.1.1 - 2025-11-13

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/teamq-ec/teamq-laravel-datatables/pull/33
* Bump stefanzweifel/git-auto-commit-action from 5 to 7 by @dependabot[bot] in https://github.com/teamq-ec/teamq-laravel-datatables/pull/35
* Bump actions/checkout from 3 to 5 by @dependabot[bot] in https://github.com/teamq-ec/teamq-laravel-datatables/pull/34

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/3.1.0...3.1.1

## 3.1.0 - 2025-05-27

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot in https://github.com/teamq-ec/teamq-laravel-datatables/pull/30
* feat: add qualify colum property by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/31

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/3.0.0...3.1.0

## 3.0.0 - 2025-04-02

### What's Changed

* fix: prevent-errors-on-properties-no-initialized by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/28
* Append query to pagination urls by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/29

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/2.1.0...3.0.0

## 2.1.1 - 2025-04-02

### What's Changed

* fix: prevent-errors-on-properties-no-initialized by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/28

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/2.1.0...2.1.1

## 2.1.0 - 2025-03-20

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/1.2.0...2.1.0

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/teamq-ec/teamq-laravel-datatables/pull/24
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/teamq-ec/teamq-laravel-datatables/pull/25
* feat: support for laravel 12 and php 8.4 by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/26

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/2.0.2...2.1.0

## 2.0.2 - 2024-10-24

### What's Changed

* Fix case sort when key is string by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/23

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/2.0.1...2.0.2

## 2.0.1 - 2024-10-18

### What's Changed

* Update documentations by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/22

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/2.0.0...2.0.1

## 2.0.0 - 2024-10-17

### What's Changed

* V2 by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/20
* Update README.md by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/21

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/1.2.0...2.0.0

## 1.2.0 - 2024-09-18

### What's Changed

* Bump dependabot/fetch-metadata from 2.1.0 to 2.2.0 by @dependabot in https://github.com/teamq-ec/teamq-laravel-datatables/pull/18
* Add scribe docs by @luilliarcec in https://github.com/teamq-ec/teamq-laravel-datatables/pull/19

**Full Changelog**: https://github.com/teamq-ec/teamq-laravel-datatables/compare/1.1.0...1.2.0
