(function($){
    $(document).on('click', '.abptb-copy-offer-code', function(e){
        e.preventDefault();
        var btn = $(this);
        var codeEl = document.getElementById('abptb-pro-offer-code');
        var code = codeEl ? codeEl.innerText : 'ABP50';
        if(navigator.clipboard){
            navigator.clipboard.writeText(code).then(function(){
                var old = btn.html();
                btn.html('<i class="fas fa-check"></i> ' + (window.abptbProOffer && abptbProOffer.copied ? abptbProOffer.copied : 'Copied'));
                setTimeout(function(){ btn.html(old); }, 1500);
            });
        }else{
            var ta = document.createElement('textarea');
            ta.value = code;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            var old = btn.html();
            btn.html('<i class="fas fa-check"></i> ' + (window.abptbProOffer && abptbProOffer.copied ? abptbProOffer.copied : 'Copied'));
            setTimeout(function(){ btn.html(old); }, 1500);
        }
    });
    $(document).on('click', '.abptb-dismiss-pro-offer', function(e){
        e.preventDefault();
        var notice = $('#abptb-pro-offer-notice');
        if(notice.length){ notice.hide(); }
        var data = new FormData();
        data.append('action','abptb_dismiss_pro_offer');
        data.append('nonce', window.abptbProOffer && abptbProOffer.nonce ? abptbProOffer.nonce : '');
        fetch(window.abptbProOffer && abptbProOffer.ajaxurl ? abptbProOffer.ajaxurl : ajaxurl, {method:'POST',credentials:'same-origin',body:data});
    });
})(jQuery);
