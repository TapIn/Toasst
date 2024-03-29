// Server JS

groupID = $('body').attr('data-groupID');
username = $('body').attr('data-username');
userID = $('body').attr('data-userID');
highlight = document.location.hash.split('#')[1];
mixpanel.identify(userID);
mixpanel.register({
    'userID': userID,
});

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

  function sendGET(data, url, done, success)
  {
    $.ajax({
      url: url,
      context: document.body,
      type: "GET",
      data: data
    }).done(function() {
    }).success(function(data){
      if(success) success(data);
      console.log(data);
      return data;
    });
  }

 function newReply(title, content, type, groupID, postID, reload){
    var replyObj = {
      "title": title,
      "content":content,
      "type":type,
      "groupID":groupID,
      "postID":postID
    }

    $("#submit-btn").attr('disabled','disabled');

    if(reload ==1)
    {
      sendPOST(replyObj,"/post/new.json", '', function(data){document.location.reload()});
    }
    else
      sendPOST(replyObj,"/post/new.json", '', function(data){document.location.href = '/g/'+ groupID + '/t/' + postID });
  }

  function newVote(self, num){
    var rs = $(self);
    num = 0;
    rs.siblings('.upvote.active').removeClass('active');
    rs.siblings('.downvote.active').removeClass('active');
    var difference = 0;

    if(rs.hasClass('active')){
      rs.removeClass('active');
      num = 0;
      if(rs.hasClass('upvote')) diff = -1;
      else if(rs.hasClass('downvote')) diff = 1;
    }

    else{
      rs.addClass('active');
      if(rs.hasClass('upvote'))
      {
        num = 1;
        diff = 1;
      }
      else {
        num = -1;
        diff = -1;
      }
    }

    var postID = rs.closest('.post-container').attr('data-postID');
    var groupID = rs.closest('.post-container').attr('data-groupID');

    vote(postID, groupID, num, self, diff);
    mixpanel.track("Vote");
  }

  function markAllNotificationsAsRead(){
    sendPOST('', '/user/notifications/mark_read.json');
    $('.notification').css('background','none').html('0');
    mixpanel.track("Notification Click");
  }

  function followGroup(){
    var fbtn = $('#follow-btn');
    if(fbtn.html()=="follow group")
    {
      sendPOST('', '/g/' + groupID + '/follow');
      fbtn.html('unfollow group');
      mixpanel.track("Follow group");
      // $('.nav-header').after('<li data-userID="'+ userID +'"><a href="/u/'+ userID+'">'+ username +'</li>');
    }
    else {
      sendPOST('', '/g/' + groupID + '/unfollow');
      fbtn.html('follow group');
      mixpanel.track("Unfollow group");
      // $('li:data(data-userID=='+ userID+')').remove();

      // $('.nav-header').siblings(".:contains('Vu Tran')").remove();
    }
  }

  function vote(postID, groupID, vote, self, diff){
   var voteObj = {
      "postID":postID,
      "groupID":groupID,
      "vote":vote
    }

    sendPOST(voteObj, "/g/"+groupID+"/t/"+ postID+"/vote.json", '', function(){ $(self).siblings('.score').html(parseInt($(self).siblings('.score').html())+diff)});
  }

  function submitNewLink(self) {
    var link = $('#link-form');
    if(link.attr('data-groupID')!= undefined) groupID = link.attr('data-groupID');
    newReply(link.find('.reply-title').val(), link.children('#link-content').attr('value'), 'link', groupID, link.attr('data-postID'));
    mixpanel.track("Submit New Link");
  }

  function submitNewComment(self){
    var link = $('#reply-form');
    if(link.attr('data-groupID')!= undefined) groupID = link.attr('data-groupID');
    newReply(link.find('.reply-title').val(), link.children('#comment-content').attr('value'), 'text', groupID, link.attr('data-postID'), link.attr('data-reload'));
    mixpanel.track("Submit New Comment");
  }

// Markup JS

  function showReplySelector(self, reload){

    var rs = $(self);
    var rf = $('#reply-form');
    rf.attr('data-postID', rs.closest('.post-container').attr('data-postID'));
    if(reload) rf.attr('data-reload', reload);

    var groupID = rs.closest('.post-container').attr('data-groupID');
    rf.attr('data-groupID', groupID);

    mixpanel.track("Submit New Text Select");

    if(rs.html().indexOf('reply') != 0){
        mixpanel.track("New Post Click");
        rs.closest('.posts-title').after(rf);
        setTimeout(function(){rf.css('opacity', 1)}, 10);
    }
    else {
      $('.reply-title').css('display','none');
        mixpanel.track("New Reply Click");
        rs.closest('.post-footer').after(rf);
        setTimeout(function(){rf.css('opacity', 1)}, 10);
    }

    if(rf.css('display') == 'block')
    {
      rf.css({
        'display' : 'none',
        'opacity' : 0
      });
    }
    else {
      rf.css({
        'display' : 'block'
      });
    }
  }

  $(document).ready(function() {
 // Highlight post
  if(highlight!=undefined) {
    var post = $('.post-container').find("[data-postID='" + highlight + "'] .container-fluid.post-body").first().addClass('active');
  }

  // Show tooltips over members
  $("[rel=tooltip]").tooltip();
  mixpanel.track('Page Load', {'page': document.location});

  // Edit Button
  $('.edit-button').click(function(){

    var container = $(this).closest('.post-container');
    var footer = $(this).closest(".container-fluid.post-footer");

    // Query for markdown
    sendGET('', '/post/markdown.json?postID=' + container.attr('data-postID'), '', (function(data){

      //Hide elements
      container.find('.post-title').css('display', 'none');
      container.find('.post-content').css('display', 'none'); 

      // Append form
      var f = $('\
        <form>\
        <textarea class="edit-title-form" name="comments" cols=3 rows=1>' + container.find('.post-title').text() +'</textarea>\
        <textarea class="edit-content-form" name="comments" cols=40 rows=6>' + data.markdown +'</textarea>\
        <a class="btn submit-edit">Submit Edit</a>\
        </form>');

      footer.before(f);

      $('.submit-edit').click((function(){
        var content = f.find('.edit-content-form').val();
        var postID = f.closest('.post-container').attr('data-postID');
        var groupID = f.closest('.post-container').attr('data-groupID');
        var title = f.find('.edit-title-form').val();
        var link = '/g/'+groupID+'/t/'+postID+'/edit';
        sendPOST({'title': title, 'content': content}, link, '', function(){document.location.reload()})
      }))(f);
    }))(container, footer);
  });

  // Container
  $('.container-fluid.post-body').mouseover(function(){
    $(this).find('.edit-dongle').css('opacity', 1);
  }).mouseout(function(){
    $(this).find('.edit-dongle').css('opacity', 0);
  });

  // Delete button
  $('.delete-button').click(function(){
    var pcontainer = $(this).closest('.post-container');
    sendPOST('', '/g/' + pcontainer.attr('data-groupID')  + '/t/' + pcontainer.attr('data-postID') + '/delete', '', function(data){document.location.reload()})
  });

  $('.new-group-btn').click(function(){
    name = $('#new-group-form').val();
    sendPOST({"name": name}, '/group/new.json', '', function(data){ window.location.href = '/g/' + data.groupID ; } );
    mixpanel.track("Create new group");
  });

  // Read more
  $('.readmore').click(function(){
    var self = $(this);
    var postID = $(this).closest('.post-container').attr('data-postID');
    sendGET({'postID': postID}, '/post/markdown/rendered.json', '', function(data){
      self.parent().html(data.html);
    });
  });
});
