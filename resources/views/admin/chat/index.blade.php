@extends('layouts.app')
@section('title', 'Live Chat & Messages')
@section('heading', 'Live Chat / Customer Support')

@section('content')
<div class="h-[calc(100vh-140px)] min-h-[580px] bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col"
     x-data="adminLiveChat()">

    <div class="flex-1 flex overflow-hidden">

        {{-- LEFT PANEL: Conversations List --}}
        <div class="w-full sm:w-80 md:w-96 border-r border-slate-200/80 flex flex-col bg-slate-50/50 flex-shrink-0"
             :class="{ 'hidden sm:flex': selectedConversation !== null, 'flex': selectedConversation === null }">

            {{-- Header & Search --}}
            <div class="p-4 bg-white border-b border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                            <i class="fab fa-whatsapp text-base"></i>
                        </div>
                        <div>
                            <h2 class="font-black text-slate-800 text-base leading-tight">Customer Inbox</h2>
                            <p class="text-[11px] text-slate-400">Live conversations</p>
                        </div>
                    </div>
                    <template x-if="totalUnread > 0">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[11px] font-black"
                              x-text="`${totalUnread} new`"></span>
                    </template>
                </div>

                {{-- Search Box --}}
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchConversations()"
                           placeholder="Search customer name or phone..."
                           class="w-full h-9.5 p-2 pl-9 pr-3 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white transition-all">
                </div>

                {{-- Filter Tabs --}}
                <div class="flex gap-1 bg-slate-100 p-1 rounded-xl text-xs font-bold">
                    <button type="button" @click="filter = 'all'"
                            class="flex-1 py-1 rounded-lg transition-all"
                            :class="filter === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        All (<span x-text="conversations.length"></span>)
                    </button>
                    <button type="button" @click="filter = 'unread'"
                            class="flex-1 py-1 rounded-lg transition-all"
                            :class="filter === 'unread' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        Unread (<span x-text="unreadConversationsCount"></span>)
                    </button>
                </div>
            </div>

            {{-- Conversations Scroll Area --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                <template x-for="conv in filteredConversations" :key="conv.id">
                    <div @click="selectConversation(conv)"
                         class="p-3.5 flex items-start gap-3 cursor-pointer transition-colors relative"
                         :class="selectedConversation && selectedConversation.id === conv.id ? 'bg-emerald-50/80 border-r-4 border-emerald-500' : 'hover:bg-white'">

                        {{-- Avatar --}}
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-slate-200 to-slate-100 text-slate-700 flex items-center justify-center font-black text-sm flex-shrink-0 border border-slate-200/60 shadow-xs relative">
                            <span x-text="getInitials(conv.customer_name)"></span>
                            <template x-if="conv.admin_unread_count > 0">
                                <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white"></span>
                            </template>
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5">
                                <h4 class="font-bold text-slate-800 text-xs truncate" x-text="conv.customer_name"></h4>
                                <span class="text-[10px] text-slate-400 flex-shrink-0" x-text="conv.formatted_time"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono mb-1 truncate" x-text="conv.customer_phone"></div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs text-slate-500 truncate"
                                   :class="{ 'font-bold text-slate-900': conv.admin_unread_count > 0 }"
                                   x-text="conv.last_message || 'Attachment'"></p>

                                <template x-if="conv.admin_unread_count > 0">
                                    <span class="px-1.5 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-black flex-shrink-0"
                                          x-text="conv.admin_unread_count"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="filteredConversations.length === 0" class="p-8 text-center text-slate-400">
                    <i class="fas fa-comments text-3xl text-slate-300 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-500">No conversations found</p>
                </div>
            </div>
        </div>

        {{-- RIGHT PANEL: Active Chat Stream & Input --}}
        <div class="flex-1 flex flex-col bg-[#ECE5DD]/30 relative"
             :class="{ 'flex': selectedConversation !== null, 'hidden sm:flex': selectedConversation === null }">

            {{-- State: No Conversation Selected --}}
            <div x-show="!selectedConversation" class="flex-1 flex flex-col items-center justify-center p-6 text-center bg-white">
                <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mb-4 border-2 border-emerald-100">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h3 class="text-lg font-black text-slate-800 mb-1">Live Customer Messaging</h3>
                <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                    Select a conversation from the left inbox to chat with customers in real time, view uploaded photos, and provide customer support.
                </p>
            </div>

            {{-- State: Conversation Selected --}}
            <template x-if="selectedConversation">
                <div class="flex-1 flex flex-col h-full overflow-hidden">

                    {{-- Chat Top Header --}}
                    <div class="p-3.5 bg-white border-b border-slate-200/80 flex items-center justify-between shadow-xs z-10">
                        <div class="flex items-center gap-3">
                            <button type="button" @click="selectedConversation = null"
                                    class="sm:hidden w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs">
                                <i class="fas fa-arrow-left"></i>
                            </button>

                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black text-sm">
                                <span x-text="getInitials(activeCustomer.customer_name)"></span>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-slate-800 text-sm" x-text="activeCustomer.customer_name"></h3>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Customer
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                                    <span class="font-mono" x-text="activeCustomer.customer_phone"></span>
                                    <span>•</span>
                                    <span>Orders: <strong class="text-slate-700" x-text="activeCustomer.order_count"></strong> (৳<span x-text="Number(activeCustomer.total_spent).toLocaleString()"></span>)</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <template x-if="activeCustomer.customer_phone && activeCustomer.customer_phone !== 'No phone'">
                                <a :href="`https://wa.me/${cleanPhone(activeCustomer.customer_phone)}`" target="_blank"
                                   class="h-8 px-3 rounded-lg bg-[#25D366]/10 text-[#25D366] hover:bg-[#25D366]/20 font-bold text-xs flex items-center gap-1.5 transition-colors">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            </template>
                            <button type="button" @click="fetchActiveMessages()"
                                    class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition-colors" title="Refresh messages">
                                <i class="fas fa-sync-alt" :class="{ 'fa-spin': isRefreshing }"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Messages Stream --}}
                    <div id="admin-chat-messages-container"
                         class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-3 bg-[#EFEAE2]/50">

                        <div class="text-center my-2">
                            <span class="px-3 py-1 rounded-full bg-white/80 border border-slate-200/80 text-[10px] font-bold text-slate-500 shadow-xs">
                                End-to-end Customer Support Chat
                            </span>
                        </div>

                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex flex-col" :class="msg.sender_type === 'admin' ? 'items-end' : 'items-start'">

                                <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl p-3 shadow-xs text-xs space-y-1 relative"
                                     :class="msg.sender_type === 'admin' ? 'bg-[#DCF8C6] text-slate-800 rounded-tr-xs' : 'bg-white text-slate-800 rounded-tl-xs border border-slate-200/60'">

                                    {{-- Sender Label --}}
                                    <div class="text-[10px] font-bold uppercase tracking-wider mb-0.5"
                                         :class="msg.sender_type === 'admin' ? 'text-emerald-700' : 'text-indigo-600'"
                                         x-text="msg.sender_type === 'admin' ? 'Support Agent' : activeCustomer.customer_name"></div>

                                    {{-- Image Attachment --}}
                                    <template x-if="msg.image_url">
                                        <div class="rounded-xl overflow-hidden cursor-pointer my-1.5 border border-black/5"
                                             @click="openLightbox(msg.image_url)">
                                            <img :src="msg.image_url" alt="Chat attachment"
                                                 class="max-h-60 w-auto object-cover rounded-xl hover:opacity-90 transition-opacity">
                                        </div>
                                    </template>

                                    {{-- Text Message --}}
                                    <template x-if="msg.message">
                                        <p class="whitespace-pre-wrap leading-relaxed text-[13px] text-slate-900" x-text="msg.message"></p>
                                    </template>

                                    {{-- Timestamp & Read Receipt --}}
                                    <div class="flex items-center justify-end gap-1 text-[10px] text-slate-400 pt-0.5">
                                        <span x-text="msg.formatted_time"></span>
                                        <template x-if="msg.sender_type === 'admin'">
                                            <i class="fas fa-check-double text-[10px]" :class="msg.is_read ? 'text-blue-500' : 'text-slate-400'"></i>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="messages.length === 0" class="p-8 text-center text-slate-400">
                            <p class="text-xs">No messages yet. Send a greeting to start the conversation!</p>
                        </div>
                    </div>

                    {{-- Image Upload Preview before sending --}}
                    <template x-if="imagePreview">
                        <div class="p-3 bg-slate-100 border-t border-slate-200 flex items-center gap-3">
                            <div class="relative w-16 h-16 rounded-xl overflow-hidden border border-slate-300 shadow-xs bg-white flex-shrink-0">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                                <button type="button" @click="removeSelectedImage()"
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="text-xs text-slate-600">
                                <span class="font-bold block">Image attached</span>
                                <span class="text-[11px] text-slate-400">Ready to send with your message</span>
                            </div>
                        </div>
                    </template>

                    {{-- Quick Response Templates / Canned Replies --}}
                    <div class="px-4 py-1.5 bg-slate-50 border-t border-slate-200/80 flex items-center gap-2 overflow-x-auto scroll-smooth text-[11px]">
                        <span class="text-slate-400 font-bold flex-shrink-0">Quick reply:</span>
                        <button type="button" @click="insertQuickReply('Hello! How can we assist you today?')"
                                class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors flex-shrink-0">
                            👋 How can we assist?
                        </button>
                        <button type="button" @click="insertQuickReply('Your order is confirmed and being processed.')"
                                class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors flex-shrink-0">
                            📦 Order confirmed
                        </button>
                        <button type="button" @click="insertQuickReply('Thank you for contacting us! Have a great day.')"
                                class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors flex-shrink-0">
                            🙏 Thank you
                        </button>
                    </div>

                    {{-- Input Bar --}}
                    <div class="p-3 bg-white border-t border-slate-200/80">
                        <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                            {{-- Attachment Button --}}
                            <label class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center cursor-pointer transition-colors flex-shrink-0"
                                   title="Attach photo">
                                <i class="fas fa-image text-sm"></i>
                                <input type="file" @change="onImageSelected($event)" accept="image/*" class="sr-only" x-ref="fileInput">
                            </label>

                            {{-- Text Input --}}
                            <input type="text" x-model="newMessage"
                                   placeholder="Type a message... (Press Enter to send)"
                                   :disabled="isSending"
                                   class="flex-1 h-10.5 p-2 rounded-xl border border-slate-200 px-4 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-slate-50 focus:bg-white transition-all">

                            {{-- Send Button --}}
                            <button type="submit"
                                    :disabled="isSending || (!newMessage.trim() && !imageFile)"
                                    class="h-10.5 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-200 flex items-center justify-center gap-1.5 disabled:opacity-50 flex-shrink-0">
                                <span x-show="!isSending"><i class="fas fa-paper-plane"></i> Send</span>
                                <span x-show="isSending"><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                        </form>
                    </div>

                </div>
            </template>
        </div>

    </div>

    {{-- LIGHTBOX MODAL FOR IMAGES --}}
    <template x-teleport="body">
        <div x-show="lightboxUrl" x-cloak
             class="fixed inset-0 z-[999999] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
             @click="lightboxUrl = null">
            <button type="button" @click="lightboxUrl = null"
                    class="absolute top-5 right-5 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 text-white text-base flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>
            <img :src="lightboxUrl" class="max-h-[90vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl" @click.stop>
        </div>
    </template>

</div>

<script>
function adminLiveChat() {
    return {
        conversations: [],
        totalUnread: 0,
        selectedConversation: null,
        activeCustomer: {},
        messages: [],
        searchQuery: '',
        filter: 'all',
        newMessage: '',
        imageFile: null,
        imagePreview: null,
        isSending: false,
        isRefreshing: false,
        lightboxUrl: null,
        pollingTimer: null,

        init() {
            this.fetchConversations();
            // Poll conversation list & active chat every 3 seconds
            this.pollingTimer = setInterval(() => {
                this.backgroundPoll();
            }, 3000);
        },

        get unreadConversationsCount() {
            return this.conversations.filter(c => c.admin_unread_count > 0).length;
        },

        get filteredConversations() {
            if (this.filter === 'unread') {
                return this.conversations.filter(c => c.admin_unread_count > 0);
            }
            return this.conversations;
        },

        getInitials(name) {
            if (!name) return 'C';
            return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        },

        cleanPhone(phone) {
            if (!phone) return '';
            let digits = phone.replace(/\D/g, '');
            if (digits.startsWith('0')) digits = '880' + digits.substring(1);
            return digits;
        },

        fetchConversations() {
            let url = '{{ route('admin.messages.conversations') }}';
            if (this.searchQuery) {
                url += `?search=${encodeURIComponent(this.searchQuery)}`;
            }

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.conversations = data.conversations || [];
                    this.totalUnread = data.total_unread || 0;
                })
                .catch(() => {});
        },

        mergeMessages(newMsgs) {
            if (!newMsgs || !newMsgs.length) return;
            const map = new Map();
            this.messages.forEach(m => {
                if (m && m.id) map.set(m.id, m);
            });
            newMsgs.forEach(m => {
                if (m && m.id) map.set(m.id, m);
            });
            this.messages = Array.from(map.values()).sort((a, b) => a.id - b.id);
        },

        selectConversation(conv) {
            this.selectedConversation = conv;
            this.activeCustomer = conv;
            this.messages = [];
            this.newMessage = '';
            this.removeSelectedImage();
            this.fetchActiveMessages(true);
        },

        fetchActiveMessages(scrollDown = false) {
            if (!this.selectedConversation) return;
            this.isRefreshing = true;

            fetch(`/admin/messages/${this.selectedConversation.id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                this.isRefreshing = false;
                this.activeCustomer = data.conversation;
                this.mergeMessages(data.messages || []);
                // Update conversation in list unread count
                const found = this.conversations.find(c => c.id === this.selectedConversation.id);
                if (found) found.admin_unread_count = 0;

                if (scrollDown) {
                    this.$nextTick(() => this.scrollToBottom());
                }
            })
            .catch(() => { this.isRefreshing = false; });
        },

        backgroundPoll() {
            this.fetchConversations();

            if (this.selectedConversation) {
                const lastId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;
                fetch(`/admin/messages/${this.selectedConversation.id}/poll?last_id=${lastId}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.messages && data.messages.length > 0) {
                        const oldLen = this.messages.length;
                        this.mergeMessages(data.messages);
                        if (this.messages.length > oldLen) {
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    }
                })
                .catch(() => {});
            }
        },

        onImageSelected(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.imageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removeSelectedImage() {
            this.imageFile = null;
            this.imagePreview = null;
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },

        insertQuickReply(text) {
            this.newMessage = text;
        },

        sendMessage() {
            if (!this.selectedConversation) return;
            if (!this.newMessage.trim() && !this.imageFile) return;

            const textToSend = this.newMessage.trim();
            const fileToSend = this.imageFile;

            this.isSending = true;
            const formData = new FormData();
            if (textToSend) formData.append('message', textToSend);
            if (fileToSend) formData.append('image', fileToSend);

            // Optimistically clear the form
            this.newMessage = '';
            this.removeSelectedImage();

            fetch(`/admin/messages/${this.selectedConversation.id}/send`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(r => r.json())
            .then(data => {
                this.isSending = false;
                if (data.success && data.message) {
                    this.mergeMessages([data.message]);
                    this.$nextTick(() => this.scrollToBottom());
                    this.fetchConversations();
                }
            })
            .catch(() => {
                this.isSending = false;
            });
        },

        scrollToBottom() {
            const container = document.getElementById('admin-chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        openLightbox(url) {
            this.lightboxUrl = url;
        }
    }
}
</script>
@endsection
