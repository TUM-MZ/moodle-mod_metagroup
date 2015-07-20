define(['jquery'], function($) {
    module = {
        initialize: function () {
            //$('#id_groups').prop( "disabled", true );
            $(function() {
                $("#id_link").on("change", function (e) {
                    module.loadGroups(this);
                });
                $("#course_filter").keyup(module.debounce(function(ev) {
                    console.log("keyup");
                    var filter_expr = $("#course_filter").val().trim();
                    console.log(filter_expr);
                    if (ev.code === 13) {
                        ev.preventDefault();
                        return
                    }
                    var atLeastOneSelected = false;
                    $("#id_link > option").each(function(index, link) {
                        console.log("Testing " + $(link).text());
                        var regex = new RegExp(".*" + filter_expr + ".*", 'i');
                        if (regex.test($(link).text())) {
                            $(link).css("display", "inline");
                            if (!atLeastOneSelected) {
                                $("#id_link")
                                    .val($(link).val())
                                    .change();
                                atLeastOneSelected = true;
                            }
                        } else {
                            $(link).css("display", "none");
                        }
                    });
                }, 350));
            });
        },
        loadGroups: function (elem) {
            $.ajax({
                url: M.cfg['wwwroot'] + '/enrol/metagroup/groups.json.php',
                type: 'POST',
                data: {
                    courseid: $(elem).val(),
                    sesskey: $('form :input[name=sesskey]').val()
                },
                success: function (res) {
                    var groups = JSON.parse(res);
                    if (Object.keys(groups).length > 0) {
                        //$('#id_courseg').prop( "disabled", false );
                        $('#id_groups')     //initialize select element
                            .find('option')
                            .remove()
                            .end()
                            .append('<option value="0">All</option>')
                            .val('0')
                        ;
                    }
                    else {
                        //$('#id_courseg').prop( "disabled", true );
                        $('#id_groups')     //initialize select element
                            .find('option')
                            .remove()
                        ;
                    }
                    Object.keys(groups).map(function (key) {
                        $('#id_groups')
                            .append($("<option></option>")
                                .attr("value", groups[key].id)
                                .text(groups[key].name))
                        ;
                    });
                }
            });
        },
        debounce: function (func, wait, options) {
            // shameslessly "stolen" from lodash
            var args,
                maxTimeoutId,
                result,
                stamp,
                thisArg,
                timeoutId,
                trailingCall,
                lastCalled = 0,
                maxWait = false,
                trailing = true;

            if (typeof func != 'function') {
                throw new TypeError(FUNC_ERROR_TEXT);
            }
            wait = wait < 0 ? 0 : (+wait || 0);
            if (options === true) {
                var leading = true;
                trailing = false;
            } else if (isObject(options)) {
                leading = !!options.leading;
                maxWait = 'maxWait' in options && nativeMax(+options.maxWait || 0, wait);
                trailing = 'trailing' in options ? !!options.trailing : trailing;
            }
            var now = Date['now'] || function() {
                    return new Date().getTime();
            };

            function isObject(value) {
                // Avoid a V8 JIT bug in Chrome 19-20.
                // See https://code.google.com/p/v8/issues/detail?id=2291 for more details.
                var type = typeof value;
                return !!value && (type == 'object' || type == 'function');
            }

            function cancel() {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                if (maxTimeoutId) {
                    clearTimeout(maxTimeoutId);
                }
                lastCalled = 0;
                maxTimeoutId = timeoutId = trailingCall = undefined;
            }

            function complete(isCalled, id) {
                if (id) {
                    clearTimeout(id);
                }
                maxTimeoutId = timeoutId = trailingCall = undefined;
                if (isCalled) {
                    lastCalled = now();
                    result = func.apply(thisArg, args);
                    if (!timeoutId && !maxTimeoutId) {
                        args = thisArg = undefined;
                    }
                }
            }

            function delayed() {
                var remaining = wait - (now() - stamp);
                if (remaining <= 0 || remaining > wait) {
                    complete(trailingCall, maxTimeoutId);
                } else {
                    timeoutId = setTimeout(delayed, remaining);
                }
            }

            function maxDelayed() {
                complete(trailing, timeoutId);
            }

            function debounced() {
                args = arguments;
                stamp = now();
                thisArg = this;
                trailingCall = trailing && (timeoutId || !leading);

                if (maxWait === false) {
                    var leadingCall = leading && !timeoutId;
                } else {
                    if (!maxTimeoutId && !leading) {
                        lastCalled = stamp;
                    }
                    var remaining = maxWait - (stamp - lastCalled),
                        isCalled = remaining <= 0 || remaining > maxWait;

                    if (isCalled) {
                        if (maxTimeoutId) {
                            maxTimeoutId = clearTimeout(maxTimeoutId);
                        }
                        lastCalled = stamp;
                        result = func.apply(thisArg, args);
                    }
                    else if (!maxTimeoutId) {
                        maxTimeoutId = setTimeout(maxDelayed, remaining);
                    }
                }
                if (isCalled && timeoutId) {
                    timeoutId = clearTimeout(timeoutId);
                }
                else if (!timeoutId && wait !== maxWait) {
                    timeoutId = setTimeout(delayed, wait);
                }
                if (leadingCall) {
                    isCalled = true;
                    result = func.apply(thisArg, args);
                }
                if (isCalled && !timeoutId && !maxTimeoutId) {
                    args = thisArg = undefined;
                }
                return result;
            }
            debounced.cancel = cancel;
            return debounced;
    }

}
    return module;
});
