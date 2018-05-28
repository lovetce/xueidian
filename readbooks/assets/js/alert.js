(function () {
    $.MsgBox = {
        Alert: function (title, msg, callback) {
            GenerateHtml("alert", title, msg);
            btnOk(callback);
            btnNo();
        },
        Confirm: function (title, msg, callback) {
            GenerateHtml("confirm", title, msg); 
            btnOk(callback);
            btnNo();
        }
    }
    var GenerateHtml = function (type, title, msg) {
        var _html = "";
        _html += '<div id="mb_box"></div><div id="mb_con">';
        _html += '<div id="mb_msg">' + msg + '</div><div id="mb_btnbox">';
        if (type == "alert") {
            _html += '<a id="mb_btn_ok">确定</a>';
        }
        if (type == "confirm") {
            _html += '<a id="mb_btn_ok">确定</a>';
            _html += '<a id="mb_btn_no">取消</a>';
        }
        _html += '</div></div>';
        $("body").append(_html);
        GenerateCss();
    }
    var GenerateCss = function () {
        $("#mb_box").css({ width: '100%', height: '100%', zIndex: '99999', position: 'fixed',
            filter: 'Alpha(opacity=60)', backgroundColor: 'black', top: '0', left: '0', opacity: '0.6'
        });
        $("#mb_con").css({ zIndex: '999999', width: '250px', position: 'fixed',
            backgroundColor: 'White', borderRadius: '8px'
        });
        $("#mb_tit").css({ display: 'block', fontSize: '14px', color: '#444', padding: '10px 15px',
            backgroundColor: '#DDD', borderRadius: '15px 15px 0 0',
            borderBottom: '3px solid #009BFE', fontWeight: 'bold'
        });
        $("#mb_msg").css({ padding: '20px', lineHeight: '20px',
            borderBottom: '1px solid #DDD', fontSize: '13px',
			textAlign:'center'
        });
        $("#mb_ico").css({ display: 'block', position: 'absolute', right: '10px', top: '9px',
             width: '18px', height: '18px', textAlign: 'center',
            lineHeight: '16px', cursor: 'pointer', fontFamily: '微软雅黑'
        });
        $("#mb_btnbox").css({ margin: '8px 0 5px 0', textAlign: 'center' });
        $("#mb_btn_ok,#mb_btn_no").css({ width: '150px', height: '25px',display:'inline-block', color: '#7eb938', border: 'none' });
        $("#mb_btn_ok").css({ backgroundColor: '#fff' });
        $("#mb_btn_no").css({ backgroundColor: 'fff', marginLeft: '20px' });
        //右上角关闭按钮hover样式
        $("#mb_ico").hover(function () {
            $(this).css({ backgroundColor: 'Red', color: 'White' });
        }, function () {
            $(this).css({ backgroundColor: '#DDD', color: 'black' });
        });
        var _widht = document.documentElement.clientWidth;  //屏幕宽
        var _height = document.documentElement.clientHeight; //屏幕高
        var boxWidth = $("#mb_con").width();
        var boxHeight = $("#mb_con").height();
        //让提示框居中
        $("#mb_con").css({ top: (_height - boxHeight) / 2 + "px", left: (_widht - boxWidth) / 2 + "px" });
    }
    //确定按钮事件
    var btnOk = function (callback) {
        $("#mb_btn_ok").click(function () {
            $("#mb_box,#mb_con").remove();
            if (typeof (callback) == 'function') {
                callback();
            }
        });
    }
    //取消按钮事件
    var btnNo = function () {
        $("#mb_btn_no,#mb_ico").click(function () {
            $("#mb_box,#mb_con").remove();
        });
    }
})();