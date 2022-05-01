let adminProjection = {
    init: function()
    {
        setTimeout( adminProjection.reloadPage, 10000 );
    },

    reloadPage: function()
    {
        location.reload();
    }
};

window.addEventListener('load', adminProjection.init);
