/**
 * Realized with PhpStorm.
 * File: /packages/toess_lab_auto_complete_search/blocks/toess_lab_auto_complete_search_block/view.js
 * Author: toesslab.ch
 * Date: 19.10.17
 * Time: 00:08
 */
(function ($) {
    var helper_replacer = function (str) {
            if (str.length === 0) {
                return str;
            }
            return str.replace(/^\s+/, '');
        },
        setValue = function (evt, val) {
            var current = evt.currentTarget,
                value,
                defVal;
            try {
                value = $.parseJSON(val);
                defVal = value.words.value;
            } catch (e) {
                defVal = val;
            }
            $(current).val(defVal);
        },
        searchValue,
        dataSource = [];
    $.each($('[id^="toesslab_query_"]'), function (i, n) {
        dataSource[i] = new Bloodhound({
            datumTokenizer: function (datum) {
                return Bloodhound.tokenizers.whitespace(datum.value);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: window.toess_lab_auto_complete_search.url_get_words + '?toesslab_query=%QUERY',
                wildcard: '%QUERY',
                filter: function (response) {
                    var d = $.map(response, function (word) {
                        return {
                            words: word
                        }
                    });
                    return d;
                }
            }
        });
        dataSource[i].initialize();
        $(n).typeahead({
                hint: true,
                highlight: window.toess_lab_auto_complete_search.high_light,
                minLength: window.toess_lab_auto_complete_search.min_length,
                classNames: window.toess_lab_auto_complete_search.classNames
            },
            {
                source: dataSource[i],
                templates: {
                    notFound: function (data) {
                        return '<div>' + window.toess_lab_auto_complete_search.noData.replace(window.toess_lab_auto_complete_search.noDataPlaceholder, data.query) + '</div>'
                    },
                    suggestion: function (data) {
                        searchValue = data._query;
                        var template = '',
                            thumb = '',
                            meta_desc = '',
                            default_attr = '',
                            desc = '';
                        if (data.words.hasOwnProperty('name') || data.words.hasOwnProperty('value')) {
                            template += '<div>';
                            template += '<p>';
                            $.each(window.toess_lab_auto_complete_search.pageAttributes, function (i, n) {
                                if (data.words.hasOwnProperty(i)) {
                                    switch (i) {
                                        case 'thumbnail':
                                            if (data.words[i].length > 0) {
                                                thumb = '<span class="' + window.toess_lab_auto_complete_search.classNames.thumbnail + '"><img src="' + $(data.words[i]).attr('href') + '" width="30">&nbsp;</span>';
                                            } else {
                                                thumb = '<span class="' + window.toess_lab_auto_complete_search.classNames.thumbnail + '"><i class="fa fa-file-image-o" aria-hidden="true"></i>&nbsp;</span>';
                                            }
                                            break;
                                        case 'meta_description':
                                            if (data.words[i].length > 0) {
                                                meta_desc += '<div class="' + window.toess_lab_auto_complete_search.classNames.meta_description + '"><b>' + n + '</b>' + '<br>' + data.words[i] + '</div>';
                                            }
                                            break;
                                        case 'description':
                                            if (data.words[i].length > 0) {
                                                desc += '<div class="' + window.toess_lab_auto_complete_search.classNames.description + '"><b>' + n + '</b>' + '<br>' + data.words[i] + '</div>';
                                            }
                                            break;
                                        default:
                                            if (data.words[i].length > 0) {
                                                default_attr += '<div class="toess_lab_auto_complete_search-default_page_attr"><b>' + n + '</b>' + '<br>' + data.words[i] + '</div>';
                                            }
                                            break
                                    }
                                }
                            });
                            template += thumb;
                            template += '</p><p>';
                            template += '<b>' + helper_replacer(data.words.name) + '</b><br>';
                            if (data.words.value === undefined) {
                                delete data.words.id;
                                data.path = data.words.cPath;
                                if (!window.toess_lab_auto_complete_search.showPath) {
                                    delete data.words.cPath;
                                }
                                delete data.words.name;
                                delete data.words.thumbnail;
                                delete data.words.meta_description;
                                delete data.words.description;
                                $.each(Object.values(data.words), function (i, n) {
                                    let br = (n.length <= 1) ? '' : '<br>',
                                        str = '';
                                    if (n.length <= window.toess_lab_auto_complete_search.cutAfter) {
                                        str = helper_replacer(n) + br;
                                    } else {
                                        str = helper_replacer(n).substring(0, window.toess_lab_auto_complete_search.cutAfter) + ' ...<br>';
                                    }
                                    template += str;
                                })
                            }
                            else if (data.words.value.constructor === Array) {
                                $.each(data.words.value, function (i, n) {
                                    if (n !== data.words.value[i + 1]) {
                                        template += helper_replacer(n).substring(0, window.toess_lab_auto_complete_search.cutAfter) + ' ...<br>';
                                    }
                                });
                                var n = template.lastIndexOf('<br>');
                                template = template.substring(0, n);
                            } else {
                                template += helper_replacer(data.words.value) + '<br>';
                            }
                            template += desc;
                            template += meta_desc;
                            template += default_attr;
                            template += '</p>';
                            template += '</div>';
                        } else {
                            template += '<span style="display: none"></span>';
                        }
                        data.words.cPath = data.path;
                        return template;
                    }
                },
                limit: window.toess_lab_auto_complete_search.limit
            }).bind('typeahead:close', function (evt) {
            setValue(evt, this.value);
        }).bind('typeahead:selected', function (evt, item) {
            var path = (window.toess_lab_auto_complete_search.root.indexOf('index.php') === -1) ? item.words.cPath.substr(1) : item.words.cPath;
            evt.preventDefault();
            setValue(evt, this.value);
            window.location.href = window.toess_lab_auto_complete_search.root + path;
            return false;
        });
    });
})(jQuery);
