// Server JS

 function sendPOST(data, url, done, success){
  console.log(data);
  $.ajax({
      url: url,
      context: document.body,
      type: "POST",
      data: data
    }).done(function() { 
    }).success(function(data){
      console.log(data);
    });
  }

 function newReply(title, content, type, groupID, postID){
    var replyObj = {
      "title": title,
      "content":content,
      "type":type,
      "groupID":groupID,
      "postID":postID
    }  
    sendPOST(replyObj,"/post/new.json");
  }

  function vote(postID, groupID, vote){
   var voteObj = {
      "postID":postID,
      "groupID":groupID,
      "vote":vote
    }  
    sendPOST(voteObj, "/post/vote.json");
  }

  function newGroup(name){
    var groupObj = { 
    }
  }

  function submitNewLink(self) {
    var link = $('#link-form');
    newReply('', link.children('#link-content').attr('value'), 'link', link.attr('data-groupID'), link.attr('data-postID'));
  }

  function submitNewComment(self){
     var link = $('#reply-form');
    newReply('', link.children('#comment-content').attr('value'), 'text', link.attr('data-groupID'), link.attr('data-postID'));
  }

// Markup JS

  function showReplySelector(self){

    var rs = $('#reply-selector');
    if(rs.css('display') == 'block')
    {
      rs.css({
        'display' : 'none',
        'opacity' : 0
      });
    }
    else {
      rs.css({
        'display' : 'block'
      });
      $(self).closest('.container-fluid').after(rs);
      setTimeout(function(){rs.css('opacity', 1)}, 10);

      $('#link-form').css({
        'display' : 'none',
        'opacity' : 0
      });

      $('#reply-form').css({
        'display' : 'none',
        'opacity' : 0
      });
    }

    if(self.className !='post-icon icon-pencil'){
      $('#reply-title').css('display','block');
    }
  }

  function selectText(self){

    var rs = $('#reply-selector').css('display', 'none');
    var rf = $('#reply-form').css('display', 'block');
    rf.attr('data-postID', rs.closest('.post-container').attr('data-postID'));
    rf.attr('data-groupID', rs.closest('.post-container').attr('data-groupID'));

    rs.after(rf);
    setTimeout(function(){rf.css('opacity', 1)}, 10);

  }

  function selectLink(self){
    var rs = $('#reply-selector').css('display', 'none');
    var rf = $('#link-form').css('display', 'block');
    rf.attr('data-postID', rs.closest('.post-container').attr('data-postID'));
    rf.attr('data-groupID', rs.closest('.post-container').attr('data-groupID'));

    rs.after(rf);
    setTimeout(function(){rf.css('opacity', 1)}, 10);
  }

  function fadeInEditDongle(self){
    $(self).find('.edit-dongle').css('opacity', .7);
  }


  function fadeOutEditDongle(self){
    $(self).find('.edit-dongle').css('opacity', 0);
  }