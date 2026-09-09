<?php

namespace TeamQ\Datatables\Concerns;

/**
 * Turns what a caller typed into a term `LIKE` matches as itself.
 *
 * `LIKE` reserves two characters — `_` for any single character, `%` for any run
 * of them — and the value of a filter is not a pattern: it is what somebody
 * reads off a row and pastes back, where an underscore is ordinary in an
 * address, a slug or an identifier. Left unescaped, `k_w@example.com` also
 * answers with `k.w@example.com` and with every `kXw@…`, and `%` on its own
 * answers with the whole table.
 *
 * The escape character itself is escaped first, or a term ending in a backslash
 * would escape the `%` the filter appends rather than being searched for, and
 * the pattern could end mid-escape — which PostgreSQL refuses outright.
 *
 * **No `ESCAPE` clause is emitted.** MySQL and PostgreSQL, the two engines this
 * package's SQL filters are written for, read a backslash as `LIKE`'s escape
 * character without being told, and saying it in the statement is not portable
 * between them: the clause takes a string literal, which the two spell
 * differently. A driver whose `LIKE` has no default escape character — SQLite,
 * SQL Server — would read `\_` as two literal characters and answer nothing
 * instead of too much; supporting one means naming the character in the clause,
 * per grammar.
 *
 * The Mongo filters never needed this: they build a BSON regex through
 * `preg_quote`, which has always treated the term as a literal.
 */
trait EscapesLikeTerms
{
    /**
     * The term as `LIKE` will read it — every reserved character standing for
     * itself.
     */
    protected function escapeLikeTerm(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }
}
