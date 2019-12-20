<div class="modal fade" id="modal-message">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" aria-hidden="true">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="asdasdasd" style="text-align: center">Send Line Message</h4>
            </div>
            <form id="modal-msg-form" class="msg-form">
                <div class="modal-body">
                    <div class="form">
                        <div class="form-group phone-modal modal-body" style="margin: 0" align="center">
                            <div class="form-group  " style="text-align: left">
                                Message
                                <div class="col-sm-12  input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <textarea name="text" value="" class="form-control" ></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="id" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">cancel</button>
                    <button type="button" class="btn btn-primary modal-msg-form" id="voice-btn">send</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function send(id){
        $('#modal-message').modal('show');
        $('input[name="id"]').val(id);
    }
    $('#close').click(function(){
        $('.modal').hide();
    })

    $('#voice-btn').click(function(){
        var text = $('textarea[name="text"]').val();
        var id = $('input[name="id"]').val();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/admin/users/sendmsg',
            data: {text : text, id : id ,_token: "{{ csrf_token() }}" },
            success: function(data) {
                req = data
                if(req['state']){
                    console.log('send success');
                }else{
                    console.log('send fail');
                }
            }
        });
    })
</script>