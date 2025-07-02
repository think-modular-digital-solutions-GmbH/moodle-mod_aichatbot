$(document).ready(function() {

    let endpoint  = M.cfg.wwwroot + '/mod/aichatbot/chat_ajax.php?sesskey=' + M.cfg.sesskey;
    let contextid = M.cfg.contextid;
    let cmid = M.cfg.contextInstanceId;
    const $userInput = $("#aichatbot-input");
    const $sendButton = $("#aichatbot-send-button");
    const $chatWindow = $(".chat-window");
    const $finishButton = $("#aichatbot-finish-button");
    let loadingArea = Y.one('.chat-window');
    let confirmationButton = $('#aichatbot-confirm-finish-button');
    let shareButton = $('.aichatbot-share-button');
    let shareConfirmationButton = $('#aichatbot-confirm-share-button');
    let revokeButton = $('.aichatbot-revoke-button');
    let revokeConrfirmationButton = $('#aichatbot-confirm-revoke-button');
    let publicButton = $('.aichatbot-public-button');
    let commentButton = $('.aichatbot-comment-button');
    let saveCommentButton = $('#aichatbot-save-comment-button');
    let spinner;
    let conversationId;

    // language strings
    const sharedsuccess = M.util.get_string('sharedsuccess', 'mod_aichatbot');
    const publicsuccess = M.util.get_string('publicsuccess', 'mod_aichatbot');
    const privatesuccess = M.util.get_string('privatesuccess', 'mod_aichatbot');
    const commentupdated = M.util.get_string('commentupdated', 'mod_aichatbot');
    const warningfinished = M.util.get_string('warningfinished', 'mod_aichatbot');

    let remaininginteractions = $('#aichatbot-remaining-interactions').val();
    if (remaininginteractions < 1) {
        $sendButton.prop('disabled', true);
        $sendButton.addClass('disabled');
        $userInput.prop('disabled', true);
        $userInput.addClass('disabled');
    }

    $userInput.focus();

    $userInput.keypress(function(e) {
        if(e.which == 13) {
            $sendButton.click();
        }
    });

    confirmationButton.click(function() {
        Y.io(endpoint,{
            method : 'POST',
            data :  build_querystring({
                action : 'confirmfinish',
                cmid : cmid
            }),
            on : {
                success : function(i, r) {
                    window.location.reload();
                },
                failure : function(i, r) {
                    console.warn(r.responseText);
                }
            },
        });
    });

    $sendButton.click(function() {
        if($userInput.val().trim() === "") {
            return;
        }

        Y.io(endpoint,{
            method : 'POST',
            data :  build_querystring({
                action : 'sendrequest',
                prompttext: $userInput.val(),
                contextid : contextid,
                cmid : cmid
            }),
            on : {
                start: function(i, r) {
                    let $messageDiv = $('<div>', {class: 'ai-chat-message message-user bg-secondary'} );
                    $chatWindow.append($messageDiv);
                    $messageDiv.text($userInput.val());
                    $userInput.val("");
                    spinner = M.util.add_spinner(Y, loadingArea);
                    spinner.show();
                    $chatWindow.scrollTop($chatWindow[0].scrollHeight);
                },
                success : function(i, r) {
                    let response = JSON.parse(r.responseText)['generatedcontent'];
                    if (response == null) {
                        console.warn(r.responseText);
                    }
                    $responseDiv = $('<div>', {class: 'ai-chat-message message-chatbot bg-dark text-white'} );
                    spinner.remove();
                    $chatWindow.append($responseDiv);
                    $responseDiv.text(response);
                    $("#aichatbot-send-button .badge").text(JSON.parse(r.responseText)['remaininginteractions']);
                    if (JSON.parse(r.responseText)['remaininginteractions'] < 1) {
                        $sendButton.prop('disabled', true);
                        $sendButton.addClass('disabled');
                        $finishButton.prop('disabled', true);
                        $finishButton.addClass('disabled');
                        $userInput.prop('disabled', true);
                        $userInput.addClass('disabled');
                        require(['core/notification'], function(notification) {
                            notification.addNotification({
                                message: warningfinished,
                                type: 'danger'
                            });
                        });
                        if ($('.activity-header').length && $('#user-notifications').length) {
                            $('#user-notifications').insertAfter('.activity-header');
                        }
                    }
                    $chatWindow.scrollTop($chatWindow[0].scrollHeight);
                    $userInput.focus();
                },
                failure : function(i, r) {
                    console.warn(r.responseText);
                }
            },
            context : this
        });
    });
    shareButton.click(function() {
        conversationId =  $(this).closest('tr').data('conversation-id');
    });

    shareConfirmationButton.click(function() {
        Y.io(endpoint,{
            method : 'POST',
            data :  build_querystring({
                action : 'shareconversation',
                conversationid : conversationId,
                cmid : cmid
            }),
            on : {
                success : function(i, r) {
                    //check if response is json
                    if (r.responseText.trim() !== "") {
                        let response = JSON.parse(r.responseText);
                        require(['core/notification'], function(notification) {
                            notification.addNotification({
                                message: response.error,
                                type: 'danger'
                            });
                        });
                        $('.close').click();
                        return;
                    }

                    sessionStorage.setItem('aichatbotShareSuccess', '1');
                    window.location.reload();
                },
                failure : function(i, r) {
                    console.warn(r.responseText);
                }
            },
        });
    });

    if (sessionStorage.getItem('aichatbotShareSuccess') === '1') {
        require(['core/notification', 'core/str'], function(notification, str) {
            notification.addNotification({
                message: sharedsuccess,
                type: 'success'
            });

            sessionStorage.removeItem('aichatbotShareSuccess');
        });
    }

    publicButton.click(function() {
        conversationId =  $(this).closest('tr').data('conversation-id');
        let $clickedButton = $(this);
        Y.io(endpoint,{
            method : 'POST',
            data :  build_querystring({
                action : 'togglepublic',
                conversationid : conversationId
            }),
            on : {
                success : function(i, r) {
                    if($clickedButton.hasClass('btn-dark')) {
                        $clickedButton.removeClass('btn-dark').addClass('btn-outline-dark');
                    }else {
                        $clickedButton.removeClass('btn-outline-dark').addClass('btn-dark');
                    }
                    require(['core/notification'], function(notification) {
                        if (r.responseText == 1) {
                            notification.addNotification({
                                message: publicsuccess,
                                type: 'success'
                            });
                        } else {
                            notification.addNotification({
                                message: privatesuccess,
                                type: 'warning'
                            });
                        }
                    });
                },
                failure : function(i, r) {
                    console.warn(r.responseText);
                }
            },
        });
    });

    revokeButton.click(function() {
        conversationId =  $(this).closest('tr').data('conversation-id');
    });

    revokeConrfirmationButton.click(function() {
        Y.io(endpoint,{
            method : 'POST',
            data :  build_querystring({
                action : 'revokeshare',
                conversationid : conversationId
            }),
            on : {
                success : function(i, r) {
                    //refresh the page
                    window.location.reload();
                }
            }
        });
    });

    commentButton.click(function() {
        conversationId =  $(this).closest('tr').data('conversation-id');

        let modal = document.getElementById('commentModal');
        let firstFocusableElement = modal.querySelector('#aichatbot-comment-textarea'); // First focusable element
        modal.setAttribute('aria-hidden', 'false'); // Make modal accessible when visible
        firstFocusableElement.focus();

        Y.io(endpoint,{
            method : 'POST',
            data :  build_querystring({
                action : 'getcomment',
                conversationid : conversationId
            }),
            on : {
                success : function(i, r) {
                    $('#aichatbot-comment-textarea').val(r.responseText);
                }
            }
        });
    });

    saveCommentButton.click(function() {
        let comment = $('#aichatbot-comment-textarea').val();

        Y.io(endpoint,{
            method : 'POST',
            data :  build_querystring({
                action : 'savecomment',
                conversationid : conversationId,
                comment : comment
            }),
            on : {
                success : function(i, r) {
                    $('.close').click();
                    $('#comment-' + conversationId).html(comment + '<div class="aichatbot-comment-button" data-toggle="modal" data-target="#commentModal"><i class="fa-solid fa-edit"></i></div>');
                    require(['core/notification'], function(notification) {
                        notification.addNotification({
                            message: commentupdated,
                            type: 'success'
                        });
                    });
                }
            }
        });
    });
});