<div class="modal fade" id="modal-voice-message">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" aria-hidden="true">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="asdasdasd" style="text-align: center">Send Line Message</h4>
            </div>
            <form id="modal-modify-voice-form" class="voice-form">
                <div class="modal-body">
                    <div class="form">
                        <div class="form-group phone-modal modal-body" style="margin: 0" align="center">
                            <div class="form-group  " style="text-align: left">
                                Message
                                <div class="col-sm-12  input-group">
                                    <span class="input-group-addon"><i class="fa fa-eye-slash fa-fw"></i></span>
                                    <textarea name="text" value="" class="form-control" ></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">cancel</button>
                    <button type="button" class="btn btn-primary modal-modify-voice-form" id="voice-btn">send</button>
                </div>
            </form>
        </div>
    </div>
</div>
