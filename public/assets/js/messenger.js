$('.chat-form').on('submit', function (e) {
    e.preventDefault()
    $.post($(this).attr('action'))
})