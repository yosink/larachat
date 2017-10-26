<template lang="html">

  <transition name="slide-left">
    <div class="chatting">

      <!-- 聊天界面头部 -->
      <div class="chatting-header">

        <div class="chatting-back">
          <i @click="$router.push('/AI')" :class="[isRedAI ? 'icon-back' : 'icon-back2']"></i>
        </div>
        <div class="chatting-title">
          <h2><i class="icon-group"></i>群聊</h2>
        </div>
        <div class="chatting-menu position">
          <i  class="icon-menu" @click="showLi = !showLi"></i>
          <ul v-show="showLi">
          <li @click="test()">个人信息</li>
          <li>查看在线列表</li>
          <li>查看历史消息</li>
          <li @click="logout()">退出</li>
            </ul>
        </div>

      </div>

      <!-- 聊天内容区域 -->
      <div @click.stop.prevent="isShowEmoji=false" ref="chattingContent" class="chatting-content">

        <div v-for="item of msgs">
          <div v-if="item.self" class="chatting-item self clearfix">
            <div class="msg-date">
              {{ item.date }}
            </div>
            <div class="msg-from">
              <span class="loc">[{{item.loc}}]</span>
              <span class="msg-author">{{ item.from}}</span>
              <img :src="item.avatarUrl" alt="">
            </div>
            <div class="msg-content">{{ item.content }}</div>
          </div>

          <div v-else class="chatting-item other clearfix">
            <div class="msg-date">
              {{ item.date }}
            </div>
            <div class="msg-from">
              <img :src="item.avatarUrl" alt="">
              <span class="loc">[{{item.loc}}]</span>
              <span class="msg-author">{{ item.from }}</span>
            </div>
            <div class="msg-content">{{ item.content }}</div>
          </div>

        </div>


      </div>

      <!-- 输入区域 -->
      <div class="chatting-input">

        <transition name="slide-bottom">
          <div v-show="isShowEmoji" class="emoji-display">
            <ul>
              <li @click="insertText(item)" v-for="item of emojis">{{item}}</li>
            </ul>
          </div>
        </transition>


        <div class="emoji">
          <i @click="showEmoji(isShowEmoji=!isShowEmoji);" class="icon-emoji"></i>
        </div>
        <div>
        <span class="error-msg" v-text="errors"></span>
</div>
        <textarea @keyup.enter="send" @input="newLine" ref="textarea" v-model.trim="inputContent"></textarea>
        <button @click="send">发送</button>
      </div>

    </div>
  </transition>

</template>

<script>
    import Cookies from 'js-cookie'
export default {
  name: 'chatting',
  data() {
    return {
      msgs: localStorage.msgs_group && JSON.parse(localStorage.msgs_group) || [],
      inputContent: '',
      oContent: {},
      oTextarea: {},
      emojis: ['😂', '🙏', '😄', '😏', '😇', '😅', '😌', '😘', '😍', '🤓', '😜', '😎', '😊', '😳', '🙄', '😱', '😒', '😔', '😷', '👿', '🤗', '😩', '😤', '😣', '😰', '😴', '😬', '😭', '👻', '👍', '✌️', '👉', '👀', '🐶', '🐷', '😹', '⚡️', '🔥', '🌈', '🍏', '⚽️', '❤️', '🇨🇳'],
      isShowEmoji: false,
      isRedAI: false,
        showLi: false,
        ws:{},
        errors:''
    }
  },
  watch: {
    msgs(val) {
      localStorage.msgs_group = JSON.stringify(val);
    }
  },
  computed: {
    name() {
      return this.$store.state.name;
    },
    avatarUrl() {
      return this.$store.state.avatarUrl;
    }
  },
  mounted() {
    this.oContent = document.querySelector('.chatting-content');
    this.oContent.scrollTop = this.oContent.scrollHeight;
    this.oTextarea = document.querySelector('textarea');
    this.ws = new WebSocket('ws://172.17.0.1:9501');
    this.ws.onopen = e => {
        if (!this.name) {
          return;
        }
        this.ws.send(JSON.stringify({"name":this.name,"cmd":"login"}));
    };
    this.ws.onmessage = e => {
        let data = JSON.parse(e.data);
        if (data.code === 102 || data.code === 104) {
            this.errors = data.msg;
            this.$refs.textarea.focus()
            return;
        }
        if (data.cmd === 'newUser') {
            let oOnline = document.createElement('div');
            oOnline.className = 'online';
            oOnline.innerText = data.name + '上线了';
            this.oContent.appendChild(oOnline);
            this.oContent.scrollTop = this.oContent.scrollHeight;
        }else if(data.cmd === 'logout') {
            Cookies.remove('name');
            this.$store.commit('changeName', '');
            this.$router.push('/login')
        }else if(data.cmd === 'fromMsg'){
            this.msgs.push(data);
        }

    };
    this.ws.onclose = e => {
        console.log('连接已断开');
        this.ws.send(JSON.stringify({cmd:'logout'}));
        this.logout();
    };

    this.oContent.scrollTop = this.oContent.scrollHeight;
  },
  methods: {
      change_showLi() {
        this.showLi = true;
      },
    send() {
      this.isShowEmoji = false;

      if (this.inputContent === '') {
        return;
      } else {
          let send_data = {
            date: this.moment().format('YYYY-MM-DD HH:mm:ss'),
              loc:localStorage.addr,
              from: Cookies.get('name'),
              content: this.inputContent,
              chanel: 0,
              cmd: 'message'
          };
          this.ws.send(JSON.stringify(send_data));
          send_data.self = true;
        this.msgs.push(send_data);
        this.inputContent = '';
        setTimeout(() => this.oContent.scrollTop = this.oContent.scrollHeight, 0);
      }
    },

    showEmoji(flag) {
      this.isShowEmoji = flag;
    },

    insertText(str) {
      str = str + ` `;
      const oTextarea = this.$refs.textarea;

      if (document.selection) {

        let sel = document.selection.createRange();

        sel.text = str;

      } else if (typeof oTextarea.selectionStart === 'number' && typeof oTextarea.selectionEnd ==='number') {

        let startPos = oTextarea.selectionStart;
        let endPos = oTextarea.selectionEnd;
        let cursorPos = startPos;
        let tempVal = oTextarea.value;
        this.inputContent = tempVal.substring(0, startPos) + str + tempVal.substring(startPos, tempVal.length)
        cursorPos += str.length;
        oTextarea.selectionStart = oTextarea.selectionEnd = cursorPos;

      } else {
        oTextarea.value += str;
      }
      this.newLine();
    },

    newLine() {
      setTimeout(() => this.oTextarea.scrollTop = this.oTextarea.scrollHeight, 0);
    },
      logout(){
          this.ws.send(JSON.stringify({name:this.name,cmd:'logout'}));
      },
      test(){
          console.log(Cookies.get('name'))
      }
  }
}
</script>

<style lang="scss" scoped>
  $blue: #2196f3;
    .position{
        position: relative;
        ul {
            position: absolute;
            top: 40px;
            right: -15px;
            width: 100px;
            background-color: rgba(0,0,0,0.5);
            border-radius: 5px;
            li {
                line-height: 30px;
                padding: 3px 10px;
                font-size: 12px;
                cursor: pointer;
                &:hover {
                    color: rgba(255,255,255,0.8)
                }
            }
        }
    }
  .chatting {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;

    .chatting-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 50px;
      width: 100%;
      background-color: $blue;
      color: white;
      padding-left: 10px;
      padding-right: 15px;

      .chatting-back {
        width: 32px;
        height: 32px;
        .icon-back {
          background: url('../common/icons/icon-ai.svg') no-repeat;
          background-size: contain;
        }
        .icon-back2 {
          background: url('../common/icons/icon-ai2.svg') no-repeat;
          background-size: contain;
        }
      }

      .chatting-title {
        i.icon-group {
          vertical-align: top;
          width: 30px;
          height: 30px;
          background: url('../common/icons/icon-group.svg') no-repeat;
          background-size: contain;
          margin-right: 3px;
        }
      }

      .chatting-menu {
        width: 30px;
        height: 30px;
        i.icon-menu {
          background: url('../common/icons/icon-index.svg') no-repeat;
          background-size: contain;
        }
      }
    }

    .chatting-content {
      flex: 1;
      width: 100%;
      background-color: rgba(0, 0, 0, .1);
      overflow: auto;
      .chatting-item {
        padding: 10px;
        width: 100%;
        .msg-date {
          text-align: center;
          color: gray;
          font-size: 80%;
        }
        .msg-from {
          display: flex;
          align-items: center;
          span.loc {
            color: gray;
            font-size: 60%;
            margin-right: 5px;
          }
          .msg-author {
            font-size: 1.2rem;
          }
          img {
            width: 30px;
            height: 30px;
            border-radius: 15px;
          }
        }
        .msg-content {
          margin-top: 5px;
          background-color: white;
          width: 200px;
          padding: 6px 10px;
          border-radius: 10px;
        }
      }

      .chatting-item + .chatting-item {
        margin-top: 10px;
      }
      .self {
        .msg-from {
          display: flex;
          justify-content: flex-end;
          align-items: center;
          img {
            margin-left: 10px;
          }
        }

        .msg-content {
          float: right;
          word-wrap: break-word;
          word-break: break-all;
          margin-right: 10px;
        }


      }

      .other {
        .msg-from {
          display: flex;
          justify-content: flex-start;
          align-items: center;
          span.loc {
            color: gray;
            font-size: 60%;
            margin-right: 5px;
          }
          img {
            margin-right: 10px;
          }
        }

        .msg-content {
          float: left;
          margin-left: 10px;
          word-wrap: break-word;
          word-break: break-all;
        }

      }

      .online {
        width: 200px;
        // max-width: 100%;
        margin: 3px auto;
        border-radius: 4px;
        text-align: center;
        background-color: #FFFDE7;
      }


    }

    .chatting-input {
      position: relative;
      display: flex;
      height: 40px;
      width: 100%;
      .emoji-display {
        position: absolute;
        width: 100%;
        height: 210px;
        background-color: white;
        top: -210px;
        left: 0;
          overflow-y: auto;
        ul {
          display: flex;
          flex-wrap: wrap;

          li {
            padding: 2px 3px;
            font-size: 2.2rem;
          }
        }
      }
      .emoji {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 45px;
        height: 100%;
        background-color: rgba(0, 0, 0, .1);
        .icon-emoji {
          width: 40px;
          height: 100%;
          background: url('../common/icons/icon-emoji.svg') no-repeat;
          background-size: contain;

        }
      }
        .error-msg{
            line-height: 20px;
            color: #880000;
        }

      textarea {
        flex: 1;
        resize: none;
        padding-left: 3px;
        padding-top: 7px;
        padding-right: 3px;
        height: 100%;
        font-size: 1.4rem;
      }
      button {
        width: 60px;
        height: 100%;
        background-color: $blue;
        color: white;
        font-size: 16px;
      }
    }
  }
</style>
