<?php

/*
Name:    gdGoogleRichSnippets
Version: 1.7.0
Author:  Milan Petrovic
Email:   milan@gdragon.info
Website: http://www.gdragon.info/

== Copyright ==

Copyright 2008-2010 Milan Petrovic (email: milan@gdragon.info)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('gdGoogleRichSnippetsGDSR')) {
    /**
     * Class for generating Google Rich Snippets elements.
     */
    class gdGoogleRichSnippetsGDSR {
        var $snippet_type;

        /**
         * Constructor
         *
         * @param string $snippet_type microformat or rdf
         */
        function gdGoogleRichSnippetsGDSR($snippet_type = "microformat") {
            $this->snippet_type = $snippet_type;
        }

        /**
         * Render snippet with thumbs rating.
         *
         * @param array $options settings for snippet
         * @return string rendered snippet code
         */
        function snippet_stars_percentage($options = array()) {
            $default = array("title" => "", "rating" => 0, "votes" => "", "review_excerpt" => "", "hidden" => true);
            $options = wp_parse_args($options, $default);

            $tpl = '';
            if ($this->snippet_type == "microformat") {
                $tpl.= '<span class="hreview-aggregate"%HIDDEN%>';
                    $tpl.= '<span class="item"><span class="fn">%TITLE%</span></span>, ';
                    $tpl.= '<span class="rating">';
                        $tpl.= '<span class="rating">%RATING%%</span>';
                        $tpl.= ' %WORD_BASEDON% <span class="votes">%VOTES%</span> %WORD_VOTES% ';
                        $tpl.= '<span class="summary">%REVIEW_EXCERPT%</span>';
                    $tpl.= '</span>';
                $tpl.= '</span>';
            } else if ($this->snippet_type == "microdata") {
                $tpl.= '<span itemscope itemtype="http://data-vocabulary.org/Review-aggregate"%HIDDEN%>';
                    $tpl.= '<span itemprop="itemreviewed"><span class="fn">%TITLE%</span></span>, ';
                    $tpl.= '<span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">';
                        $tpl.= '<span itemprop="rating">%RATING%%</span>';
                        $tpl.= ' %WORD_BASEDON% <span itemprop="votes">%VOTES%</span> %WORD_VOTES% ';
                    $tpl.= '</span>';
                $tpl.= '</span>';
            } else if ($this->snippet_type == "rdf") {
                $tpl.= '<div xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Review-aggregate"%HIDDEN%>';
                    $tpl.= '<span property="v:itemreviewed">%TITLE%</span>, ';
                    $tpl.= '<span rel="v:rating">';
                        $tpl.= '<span typeof="v:Rating">';
                            $tpl.= '<span property="v:rating">%RATING%%</span>';
                        $tpl.= '</span>';
                    $tpl.= '</span>';
                    $tpl.= ' %WORD_BASEDON% <span property="v:votes">%VOTES%</span> %WORD_VOTES% ';
                    $tpl.= '<span property="v:summary">%REVIEW_EXCERPT%</span>';
                $tpl.= '</div>';
            }

            $tpl = apply_filters("gdsr_snippet_template_stars_percentage", $tpl, $this->snippet_type);

            $votes = $options["votes"];
            $hidden = $options["hidden"] ? ' style="display: none !important;"' : '';

            $tpl = str_replace("%HIDDEN%", $hidden, $tpl);
            $tpl = str_replace("%WORD_BASEDON%", __("based on", "gd-star-rating"), $tpl);
            $tpl = str_replace("%WORD_VOTES%", _n("rating", "ratings", $votes, "gd-star-rating"), $tpl);
            $tpl = str_replace("%TITLE%", $options["title"], $tpl);
            $tpl = str_replace("%RATING%", $options["rating"], $tpl);
            $tpl = str_replace("%VOTES%", $votes, $tpl);
            $tpl = str_replace("%REVIEW_EXCERPT%", $options["review_excerpt"], $tpl);

            return $tpl;
        }

        /**
         * Render snippet with rating.
         *
         * @param array $options settings for snippet
         * @return string rendered snippet code
         */
        function snippet_stars_rating($options = array()) {
            $default = array("title" => "", "rating" => 0, "max_rating" => 5, "votes" => "", "review_excerpt" => "", "hidden" => true);
            $options = wp_parse_args($options, $default);

            $tpl = '';
            if ($this->snippet_type == "microformat") {
                $tpl.= '<span class="hreview-aggregate"%HIDDEN%>';
                    $tpl.= '<span class="item"><span class="fn">%TITLE%</span></span>, ';
                    $tpl.= '<span class="rating">';
                        $tpl.= '<span class="average">%RATING%</span> %WORD_OUTOF% ';
                        $tpl.= '<span class="best">%MAX_RATING%</span>';
                        $tpl.= ' %WORD_BASEDON% <span class="votes">%VOTES%</span> %WORD_VOTES% ';
                        $tpl.= '<span class="summary">%REVIEW_EXCERPT%</span>';
                    $tpl.= '</span>';
                $tpl.= '</span>';
            } else if ($this->snippet_type == "microdata") {
                $tpl.= '<span itemscope itemtype="http://data-vocabulary.org/Review-aggregate"%HIDDEN%>';
                    $tpl.= '<span itemprop="itemreviewed"><span class="fn">%TITLE%</span></span>, ';
                    $tpl.= '<span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">';
                        $tpl.= '<span itemprop="average">%RATING%</span> %WORD_OUTOF% ';
                        $tpl.= '<span itemprop="best">%MAX_RATING%</span>';
                        $tpl.= ' %WORD_BASEDON% <span itemprop="votes">%VOTES%</span> %WORD_VOTES% ';
                    $tpl.= '</span>';
                $tpl.= '</span>';
            } else if ($this->snippet_type == "rdf") {
                $tpl.= '<div xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Review-aggregate"%HIDDEN%>';
                    $tpl.= '<span property="v:itemreviewed">%TITLE%</span>, ';
                    $tpl.= '<span rel="v:rating">';
                        $tpl.= '<span typeof="v:Rating">';
                            $tpl.= '<span property="v:average">%RATING%</span> %WORD_OUTOF% ';
                            $tpl.= '<span property="v:best">%MAX_RATING%</span>';
                        $tpl.= '</span>';
                    $tpl.= '</span>';
                    $tpl.= ' %WORD_BASEDON% <span property="v:votes">%VOTES%</span> %WORD_VOTES% ';
                    $tpl.= '<span property="v:summary">%REVIEW_EXCERPT%</span>';
                $tpl.= '</div>';
            }

            $tpl = apply_filters("gdsr_snippet_template_stars_rating", $tpl, $this->snippet_type);

            $votes = $options["votes"];
            $hidden = $options["hidden"] ? ' style="display: none !important;"' : '';

            $tpl = str_replace("%HIDDEN%", $hidden, $tpl);
            $tpl = str_replace("%WORD_BASEDON%", __("based on", "gd-star-rating"), $tpl);
            $tpl = str_replace("%WORD_OUTOF%", __("out of", "gd-star-rating"), $tpl);
            $tpl = str_replace("%WORD_VOTES%", _n("rating", "ratings", $votes, "gd-star-rating"), $tpl);
            $tpl = str_replace("%TITLE%", $options["title"], $tpl);
            $tpl = str_replace("%RATING%", $options["rating"], $tpl);
            $tpl = str_replace("%MAX_RATING%", $options["max_rating"], $tpl);
            $tpl = str_replace("%VOTES%", $votes, $tpl);
            $tpl = str_replace("%REVIEW_EXCERPT%", $options["review_excerpt"], $tpl);

            return $tpl;
        }

        /**
         * Render snippet with review.
         *
         * @param array $options settings for snippet
         * @return string rendered snippet code
         */
        function snippet_stars_review($options = array()) {
            $default = array("title" => "", "rating" => 0, "max_rating" => 5, "reviewer" => "", "review_date" => "", "review_excerpt" => "", "hidden" => true);
            $options = wp_parse_args($options, $default);

            $tpl = '';
            if ($this->snippet_type == "microformat") {
                $tpl.= '<span class="hreview"%HIDDEN%>';
                    $tpl.= '<span class="item"><span class="fn">%TITLE%</span></span>, ';
                    $tpl.= '<span class="rating">';
                        $tpl.= '%WORD_REVIEWEDBY% <span class="reviewer">%REVIEWER%</span>';
                        $tpl.= ' %WORD_ON% <span class="dtreviewed">%REVIEW_DATE%</span>';
                        $tpl.= '<span class="summary">%REVIEW_EXCERPT%</span>';
                        $tpl.= ' %WORD_RATING% <span class="value">%RATING%</span>';
                        $tpl.= ' %WORD_OUTOF% <span class="best">%MAX_RATING%</span>';
                    $tpl.= '</span>';
                $tpl.= '</span>';
            } else if ($this->snippet_type == "microdata") {
                $tpl.= '<span itemscope itemtype="http://data-vocabulary.org/Review"%HIDDEN%>';
                    $tpl.= '<span itemprop="itemreviewed">%TITLE%</span>, ';
                    $tpl.= '%WORD_REVIEWEDBY% <span itemprop="reviewer">%REVIEWER%</span>';
                    $tpl.= ' %WORD_ON% <time itemprop="dtreviewed" datetime="%REVIEW_DATE%">%REVIEW_DATE%</time>';
                    $tpl.= '<span itemprop="summary">%REVIEW_EXCERPT%</span>';
                    $tpl.= ' %WORD_RATING% <span itemprop="rating">%RATING%</span>';
                    $tpl.= ' %WORD_OUTOF% <span class="best">%MAX_RATING%</span>';
                $tpl.= '</span>';
            } else if ($this->snippet_type == "rdf") {
                $tpl.= '<div xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Review"%HIDDEN%>';
                    $tpl.= '<span property="v:itemreviewed">%TITLE%</span>, ';
                    $tpl.= '%WORD_REVIEWEDBY% <span property="v:reviewer">%REVIEWER%</span>';
                    $tpl.= ' %WORD_ON% <span property="v:dtreviewed" content="%REVIEW_DATE%">%REVIEW_DATE%</span>';
                    $tpl.= '<span property="v:summary">%REVIEW_EXCERPT%</span>';
                    $tpl.= ' %WORD_RATING% <span property="v:rating">%RATING%</span>';
                    $tpl.= ' %WORD_OUTOF%<span property="v:best">%MAX_RATING%</span>';
                $tpl.= '</div>';
            }

            $tpl = apply_filters("gdsr_snippet_template_stars_review", $tpl, $this->snippet_type);
            $hidden = $options["hidden"] ? ' style="display: none !important;"' : '';

            $tpl = str_replace("%HIDDEN%", $hidden, $tpl);
            $tpl = str_replace("%WORD_REVIEWEDBY%", __("reviewed by", "gd-star-rating"), $tpl);
            $tpl = str_replace("%WORD_ON%", __("on", "gd-star-rating"), $tpl);
            $tpl = str_replace("%WORD_RATING%", __("rating", "gd-star-rating"), $tpl);
            $tpl = str_replace("%WORD_OUTOF%", __("out of", "gd-star-rating"), $tpl);
            $tpl = str_replace("%TITLE%", $options["title"], $tpl);
            $tpl = str_replace("%RATING%", $options["rating"], $tpl);
            $tpl = str_replace("%MAX_RATING%", $options["max_rating"], $tpl);
            $tpl = str_replace("%REVIEWER%", $options["reviewer"], $tpl);
            $tpl = str_replace("%REVIEW_DATE%", $options["review_date"], $tpl);
            $tpl = str_replace("%REVIEW_EXCERPT%", $options["review_excerpt"], $tpl);

            return $tpl;
        }
    }
}

?>