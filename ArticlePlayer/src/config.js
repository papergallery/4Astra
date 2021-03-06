define([], function () {
    window.requirejs.config({
        paths: {
            "rslides": M.cfg.wwwroot + '/mod/longread/js/rslides.min',
            "touch_punch": M.cfg.wwwroot + '/mod/longread/js/touch-punch.min'
        },
        shim: {
            'rslides': ['jquery'],
            "touch_punch": ['jquery', 'jqueryui']
        }
    });
});