$('.post-container').appear(function(){
    var self = $(this);
    if (self.attr('data-read') == 'false') {
        $.ajax({
            type: 'POST',
            url: '/g/' + self.attr('data-groupID') + '/t/' + self.attr('data-postID') + '/mark_read.json',
            dataType: 'json'
        });
        self.attr('data-read', 'true');
    }
});
