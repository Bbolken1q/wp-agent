const SHOW = Symbol("show")
const HIDE = Symbol("hide")

const QUESTION = Symbol("message")
const ANSWER = Symbol("answer")

const DISABLED = Symbol("message")
const ENABLED = Symbol("answer")

var isDisabled = false;
var wpAgentApiAddress = (typeof wpAgentData !== 'undefined') ? wpAgentData.wpAgentAdress : ''

const wpAgentEnableDisableAskbox = (state) => {
    if (typeof state != "symbol")
        return;

    switch (state) {
        case ENABLED:
            isDisabled = false
            document.getElementById("submitButton").disabled = false // visual indicator
            break;
        case DISABLED:
            isDisabled = true
            document.getElementById("submitButton").disabled = true
    }

}

const wpAgentQuery = () => {

    if (isDisabled) {
        console.log("returning on disabled")
        return;
    }

    let query = document.getElementById("wpAgentInputBox").value
    if (query == "") {
        console.log("returning on empty query")
        return;
    }
    
    // function not returning here means we are good to go
    wpAgentEnableDisableAskbox(DISABLED)

    document.getElementById("wpAgentInputBox").value = ""
    wpAgentCreateMessage(QUESTION, query)

    const url = wpAgentApiAddress

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            // 'Access-Control-Allow-Origin': '*'
        },
        body: JSON.stringify({ query: query })
    }).then(ans => ans.json()).then(data => {
        wpAgentCreateMessage(ANSWER, data)
        wpAgentEnableDisableAskbox(ENABLED)
    })
}

const wpAgentCreatePopup = () => {
    let dialog = document.createElement('div')
    dialog.className = 'wpagent_dialog'
    dialog.id = 'wpagent_dialog'

    let messageWindow = document.createElement('div')
    messageWindow.className = 'wpagent_messages'
    messageWindow.id = 'wpagent_messages'

    let chatbox = document.createElement('div')
    chatbox.className = 'wpagent_chatbox'
    chatbox.innerHTML = '<form onsubmit="event.preventDefault(); wpAgentQuery()"> <input id="wpAgentInputBox" placeholder="Zadaj pytanie" autocomplete="off" maxlength="100"/><button type="submit" class="fas fa-arrow-right" id="submitButton"></button> </form>'
    chatbox.id = 'wpagent_chatbox'

    dialog.appendChild(messageWindow)
    dialog.appendChild(chatbox)

    document.body.appendChild(dialog)
}

const wpAgentCreateMessage = (type, content) => {
    if (typeof type != "symbol")
        return;
    let messagebox = document.getElementById('wpagent_messages')
    if (!messagebox)
        return;

    let wrapper = document.createElement('div') 
    wrapper.classList.add('wpagent_message_wrapper')
    let message = document.createElement('div')
    message.classList.add('wpagent_message')
    switch (type) {
        case QUESTION:
            message.classList.add('wpagent_question')
            break;
        case ANSWER:
            message.classList.add('wpagent_answer')
    }

    message.innerText = content

    wrapper.appendChild(message)
    messagebox.appendChild(wrapper)
    wpAgentScrollToBottom()
}

const wpAgentChangePopupDisplay = (dialog, display) => {
    if (typeof display != "symbol")
        throw new TypeError("the \'display\' parameter is not of valid type")

    switch (display) { // this whole function should be written in typescript
        case SHOW:
            dialog.style.display = '';
            break;
        case HIDE:
            dialog.style.display = 'none';
            break;
    }
}

const wpAgentScrollToBottom = () => {
    let messagebox = document.getElementById('wpagent_messages')
    if (!messagebox) return;

    messagebox.scrollTo(0, messagebox.scrollHeight)
}

// keep the specific strings out of the main business function
const wpAgentIsDialogHidden = (dialog) => {
    return dialog.style.display === 'none'
}

const wpAgentShowHidePopup = () => {
    let activeDialog = document.getElementById('wpagent_dialog')

    // lazily create dialog on first click
    // this is lazy for no actual reason other than "i am stupid and 
    // wanted to create and remove the popup every time somebody clicks the widget"
    if (!activeDialog) {
        wpAgentCreatePopup()
        wpAgentCreateMessage(ANSWER, "Cześć! Z przyjemnością pomogę ci w każdej sprawie!")
        return;
    }

    // if a dialog exists, just check if it's being displayed or not
    if (wpAgentIsDialogHidden(activeDialog)) {
        wpAgentChangePopupDisplay(activeDialog, SHOW)
        wpAgentScrollToBottom()

    } else {
        wpAgentChangePopupDisplay(activeDialog, HIDE)
    }
}

// init
document.addEventListener('DOMContentLoaded', () => {
    let widget = document.createElement("div")
    widget.classList.add('wpagent_widget')
    widget.id = 'wpagent_widget'
    
    let image = document.createElement('img')
    image.classList.add('wpagent_widget-image')
    image.src = (typeof wpAgentData !== 'undefined') ? wpAgentData.iconUrl : ''

    widget.appendChild(image)
    document.body.appendChild(widget)


    if (!widget)
        return;
    widget.addEventListener('click', wpAgentShowHidePopup)
})