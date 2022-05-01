let adminIndex = {
    currentModal: {
        message: '',
        target: ''
    },

    init: function()
    {
        $(".modal-confirm").each(function() {
            $( this ).on("click", adminIndex.modalConfirmClick);
        });

        $("#btn-modal-yes").on("click", adminIndex.onModalYes);
        $("#btn-modal-no").on("click", adminIndex.onModalNo);
    },

    modalConfirmClick: function(e)
    {
        e.stopPropagation();

        adminIndex.currentModal.message = e.target.dataset.modalMessage;
        adminIndex.currentModal.target = e.target.href;

        $("#modal-overlay-screen").removeClass("dn");
        $("#modal-overlay-message").html(e.target.dataset.modalMessage);

        return false;
    },

    onModalYes: function()
    {
        window.location = adminIndex.currentModal.target;
    },

    onModalNo: function()
    {
        $("#modal-overlay-message").html("");
        $("#modal-overlay-screen").addClass("dn");
    }
};

window.addEventListener('load', adminIndex.init);
