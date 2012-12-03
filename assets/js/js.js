// Server JS

groupID = $('body').attr('data-groupID');
username = $('body').attr('data-username');
userID = $('body').attr('data-userID');

 function sendPOST(data, url, done, success){
  console.log(url + " : " + data);
  $.ajax({
      url: url,
      context: document.body,
      type: "POST",
      data: data
    }).done(function() { 
    }).success(function(data){
      console.log(data);
      if(success) success(data);
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
    sendPOST(replyObj,"/post/new.json", '', function(){document.location.reload()}); 
  }

  function newVote(self, num){
    var rs = $(self);
    num = 0;
    rs.siblings('.upvote.active').removeClass('active');
    rs.siblings('.downvote.active').removeClass('active');
    
    if(rs.hasClass('active')){
      rs.removeClass('active');
      num = 0;
    }

    else{
      rs.addClass('active');
      if(rs.hasClass('upvote'))
      {
        num = 1;
      }
      else {
        num = -1;
      }
    }
    var postID = rs.closest('.post-container').attr('data-postID');
    var groupID = rs.closest('.post-container').attr('data-groupID');

    vote(postID, groupID, num, self);
  }

  function markAllNotificationsAsRead(){
    sendPOST('', '/user/notifications/mark_read.json');
    $('.notification').css('background','none').html('0');
  }

  function followGroup(){
    var fbtn = $('#follow-btn');
    if(fbtn.html()=="follow group")
    {
      sendPOST('', '/g/' + groupID + '/follow');
      fbtn.html('unfollow group');
      $('.nav-header').after('<li data-userID="'+ userID +'"><a href="/u/'+ userID+'">'+ username +'</li>');
    }
    else {
      sendPOST('', '/g/' + groupID + '/unfollow');
      fbtn.html('follow group');
      $('li:data(data-userID=='+ userID+')').remove();

      // $('.nav-header').siblings(".:contains('Vu Tran')").remove();
    }
  }

  function vote(postID, groupID, vote, self){
   var voteObj = {
      "postID":postID,
      "groupID":groupID,
      "vote":vote
    }

    sendPOST(voteObj, "/g/"+groupID+"/t/"+ postID+"/vote.json", '', function(){ $(self).siblings('.score').html(parseInt($(self).siblings('.score').html())+vote)});
  }

  function newGroup(){
    name = $('#new-group-form').val();
    var postObj = {
      "name": name
    }  
    sendPOST(postObj, '/group/new.json', '', function(data){ window.location.href = '/g/' + data.groupID ; } );
    
  }

  function submitNewLink(self) {
    var link = $('#link-form');
    if(link.attr('data-groupID')!= undefined) groupID = link.attr('data-groupID');
    newReply(link.find('.reply-title').val(), link.children('#link-content').attr('value'), 'link', groupID, link.attr('data-postID'));
  }

  function submitNewComment(self){
    var link = $('#reply-form');
    if(link.attr('data-groupID')!= undefined) groupID = link.attr('data-groupID');
    newReply(link.find('.reply-title').val(), link.children('#comment-content').attr('value'), 'text', groupID, link.attr('data-postID'));
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
      $('.reply-title').css('display','block');
    }
    else {
      $('.reply-title').css('display','none');
    }
  }

  function selectText(self){

    var rs = $('#reply-selector').css('display', 'none');
    var rf = $('#reply-form').css('display', 'block');
    rf.attr('data-postID', rs.closest('.post-container').attr('data-postID'));

    var groupID = rs.closest('.post-container').attr('data-groupID');
    rf.attr('data-groupID', groupID);

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