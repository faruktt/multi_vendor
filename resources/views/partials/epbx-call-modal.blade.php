{{-- ══════════════════════════════════════════════════════════════════════════
     096XX CLOUD PBX & IP TELEPHONY CALL CENTER MODAL
     Browser WebRTC Softphone + MicroSIP Direct Dial + AI Order Verification
     ══════════════════════════════════════════════════════════════════════════ --}}
@php
    $epbxBaseUrl = config('services.epbx.base_url', 'https://ebsbazarbd.epbx.bd/api/v1');
    $epbxHost    = parse_url($epbxBaseUrl, PHP_URL_HOST) ?? 'ebsbazarbd.epbx.bd';
    $epbxDialer  = config('services.epbx.dialer_url', 'https://ebsbazarbd.epbx.bd/dialer');
@endphp
<div x-data="epbxCallCenter()"
     x-cloak
     x-show="isOpen"
     @open-epbx-modal.window="open($event.detail)"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
     aria-labelledby="epbx-modal-title" role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"></div>

    {{-- Modal Dialog --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         @click.outside="close()"
         class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-xl max-h-[92vh] flex flex-col overflow-hidden z-10">

        {{-- ── MODAL HEADER ── --}}
        <div class="relative px-6 py-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex items-center justify-between overflow-hidden">
            {{-- Ambient light effect --}}
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-blue-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30 ring-2 ring-white/20 flex-shrink-0">
                    <i class="fas fa-phone-volume text-white text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 id="epbx-modal-title" class="font-bold text-[14.5px] tracking-tight text-white flex items-center gap-2">
                            096XX Cloud PBX / IP Call
                        </h3>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-2 py-0.2 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live PBX Ready
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-300 mt-0.5">
                        Direct PC calling · Automated AI Order Verification Call
                    </p>
                </div>
            </div>

            <button type="button" @click="close()"
                    class="relative z-10 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-all cursor-pointer"
                    title="Close Window (Esc)">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- ── CUSTOMER & ORDER HERO CARD ── --}}
        <div class="bg-gradient-to-b from-slate-50 to-white px-6 py-3 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-xs">
                {{-- Customer Info --}}
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm flex-shrink-0 border border-emerald-100">
                        <span x-text="(customerName || 'C').charAt(0).toUpperCase()"></span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-slate-800 text-[13.5px] truncate" x-text="customerName || 'Customer'"></p>
                            <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700" x-text="'#' + invoiceNo"></span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono font-bold text-[14px] text-slate-800" x-text="customerPhone"></span>
                            <button type="button" @click="copyPhone()"
                                    class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded hover:bg-slate-100 cursor-pointer"
                                    title="Copy Phone Number">
                                <i class="fas fa-copy text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Order Total --}}
                <div class="flex items-center sm:flex-col sm:items-end justify-between border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                    <span class="text-[10.5px] text-slate-400 uppercase tracking-wider font-semibold">Total Order Amount</span>
                    <span class="font-black text-slate-900 text-base sm:text-lg flex items-center gap-0.5">
                        <span class="text-xs text-slate-500 font-medium">৳</span>
                        <span x-text="orderAmount"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- ── MODAL SCROLLABLE CONTENT ── --}}
        <div class="px-6 py-4 overflow-y-auto space-y-4 flex-1">

            {{-- ══ OPTION 1: BROWSER CALLING (Official Web Dialer & WebRTC) ══ --}}
            <div class="relative bg-gradient-to-br from-indigo-50/80 via-sky-50/40 to-slate-50 rounded-2xl p-4 border border-indigo-200 shadow-xs">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                            <i class="fas fa-globe"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-900 text-[13.5px] flex items-center gap-2">
                                <span>1. Browser Calling</span>
                                <span class="text-[9.5px] font-bold px-2 py-0.2 rounded-full"
                                      :class="webrtcConnected ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                                      x-text="webrtcConnected ? 'WebRTC Online' : 'Direct Dial'"></span>
                            </h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Dial directly using official ePBX Web Dialer or in-browser WebRTC softphone.
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="showWebrtcSettings = !showWebrtcSettings"
                            class="text-xs text-indigo-600 hover:text-indigo-800 p-1.5 rounded-lg hover:bg-indigo-100/60 transition-colors cursor-pointer"
                            title="WebRTC Extension Settings">
                        <i class="fas fa-gear"></i>
                    </button>
                </div>

                {{-- Action Buttons: Official Web Dialer (Recommended) & In-Page WebRTC --}}
                <div class="space-y-2">
                    <button type="button" @click="openPcWebDialer()"
                            class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-headset text-xs"></i>
                        <span>Open ePBX Web Dialer (Official Dialer) ↗</span>
                    </button>

                    <button type="button" @click="callViaWebRtc()"
                            :disabled="webrtcCalling"
                            class="w-full bg-white hover:bg-slate-50 text-indigo-700 border border-indigo-200 font-bold text-xs py-2 px-3 rounded-xl transition-all shadow-2xs flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-phone-volume text-xs" :class="{ 'fa-spin fa-spinner': webrtcCalling }"></i>
                        <span x-text="webrtcCalling ? 'Dialing...' : 'In-Browser WebRTC Call (SIP.js)'"></span>
                    </button>
                </div>

                {{-- Helpful info about Dialer and Rejections --}}
                <div class="mt-2.5 text-[10.5px] text-slate-500 bg-white/80 p-2 rounded-xl border border-indigo-100 flex items-start gap-1.5 shadow-2xs">
                    <i class="fas fa-circle-info text-indigo-500 mt-0.5 flex-shrink-0"></i>
                    <span>Allow <strong>Microphone Access</strong> in Web Dialer and ensure status is <strong>Online</strong>. If rejected, check Outbound Routes and balance on ePBX dashboard.</span>
                </div>

                {{-- Settings Panel (Extension & Password) --}}
                <div x-show="showWebrtcSettings" x-cloak class="mt-3 p-3 bg-white rounded-xl border border-indigo-100 text-xs space-y-2">
                    <p class="font-bold text-slate-700 text-[11.5px] flex items-center justify-between">
                        <span>WebRTC Extension Credentials (Save Once):</span>
                        <span class="text-[10px] text-slate-400 font-normal">Asterisk Extension</span>
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10.5px] text-slate-500 mb-0.5">Extension Number</label>
                            <input type="text" x-model="webrtcExtension" placeholder="e.g. 101"
                                   class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10.5px] text-slate-500 mb-0.5">Secret Password</label>
                            <input type="password" x-model="webrtcPassword" placeholder="Password"
                                   class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="saveWebrtcCredentials()"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] px-3 py-1.5 rounded-lg cursor-pointer transition-colors">
                            Save & Connect
                        </button>
                    </div>
                </div>

                {{-- In-Call status banner --}}
                <div x-show="webrtcStatus" x-cloak class="mt-2.5 text-[11px] p-2 rounded-lg bg-indigo-100/80 text-indigo-900 flex items-center justify-between">
                    <span x-text="'Status: ' + webrtcStatus"></span>
                    <button type="button" x-show="webrtcInCall" @click="hangupWebRtc()"
                            class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-2 py-0.5 rounded text-[10.5px] cursor-pointer">
                        Hang Up
                    </button>
                </div>
            </div>

            {{-- ══ OPTION 2: DIRECT PC SOFTPHONE (MicroSIP / Zoiper) ══ --}}
            <div class="relative bg-gradient-to-br from-emerald-50/70 via-teal-50/30 to-slate-50 rounded-2xl p-4 border border-emerald-200 shadow-xs">
                <div class="flex items-start justify-between gap-3 mb-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                            <i class="fas fa-desktop"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-900 text-[13.5px]">2. Call via Desktop Softphone (MicroSIP / Zoiper)</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Click to trigger instant dialing in MicroSIP / Zoiper.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="dialViaSoftphone('sip')"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 px-3 rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fas fa-phone-flip text-xs"></i>
                        <span>MicroSIP (sip:)</span>
                    </button>

                    <button type="button" @click="dialViaSoftphone('tel')"
                            class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs py-2.5 px-3 rounded-xl shadow-2xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fas fa-phone text-xs"></i>
                        <span>Windows (tel:)</span>
                    </button>
                </div>

                {{-- Clear Notice about Chrome Popup --}}
                <div class="mt-2 text-[10.5px] text-slate-500 bg-white/70 p-2 rounded-lg border border-slate-100 flex items-start gap-1.5">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <span>MicroSIP dials instantly when running on your PC. In the browser popup, check <strong>"Always allow"</strong> to dial automatically in the future.</span>
                </div>
            </div>

            {{-- ══ OPTION 3: AUTOMATED AI VOICE VERIFICATION (ePBX API) ══ --}}
            <div class="relative bg-gradient-to-br from-violet-50/70 via-purple-50/30 to-slate-50 rounded-2xl p-4 border border-purple-100 shadow-xs">
                <div class="flex items-start justify-between gap-3 mb-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-violet-600 text-white flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                            <i class="fas fa-robot"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-900 text-[13.5px]">3. Automated AI Voice Order Verification (AI Voice)</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                AI automatically dials the customer and verifies the order totaling <strong class="text-slate-700 font-semibold" x-text="'৳' + orderAmount"></strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <button type="button" @click="sendAiVerificationCall()"
                        :disabled="isCalling"
                        class="w-full bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 disabled:opacity-60 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fas fa-phone-arrow-up-right text-xs" :class="{ 'fa-spin fa-spinner': isCalling }"></i>
                    <span x-text="isCalling ? 'Dispatching Call...' : 'Send AI Verification Call (ElevenLabs TTS)'"></span>
                </button>

                {{-- Live Call Status Feedback --}}
                <div x-show="callStatus" x-cloak class="mt-2.5">
                    <template x-if="callStatus === 'success'">
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-2 shadow-2xs">
                            <i class="fas fa-circle-check text-emerald-500 text-sm mt-0.5 flex-shrink-0"></i>
                            <div class="flex-1">
                                <p class="font-bold" x-text="callMessage || 'Call dispatched successfully!'"></p>
                                <p class="text-[10.5px] text-emerald-700 mt-0.5">Automated AI voice call is ringing on customer's phone.</p>
                            </div>
                        </div>
                    </template>

                    <template x-if="callStatus === 'insufficient_balance'">
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-2xs">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-triangle-exclamation text-amber-500 text-sm mt-0.5 flex-shrink-0"></i>
                                <div class="flex-1">
                                    <p class="font-bold text-[11.5px] text-amber-900">Insufficient ePBX Wallet Balance!</p>
                                    <p class="text-[10.5px] text-amber-800 mt-0.5" x-text="callMessage"></p>
                                    <div class="mt-1.5">
                                        <a href="https://{{ $epbxHost }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-[10px] px-2 py-1 rounded transition-colors">
                                            <span>Recharge ePBX Wallet ↗</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="callStatus === 'tts_unavailable'">
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-xs shadow-2xs">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-robot text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
                                <div class="flex-1">
                                    <p class="font-bold text-[12px] text-amber-900">Temporary Issue with ePBX AI Voice (TTS)</p>
                                    <p class="text-[11px] text-amber-800 mt-1 leading-relaxed">
                                        ePBX ElevenLabs AI Voice service is currently unresponsive. <strong class="text-emerald-700 font-bold underline">Deducted fee has been refunded to your wallet.</strong>
                                    </p>
                                    <p class="text-[10.5px] text-slate-600 mt-1.5">
                                        💡 Quick alternative: Use <strong>"ePBX Web Dialer"</strong> or <strong>"MicroSIP"</strong> above to call the customer directly.
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <button type="button" @click="openPcWebDialer()"
                                                class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10.5px] px-2.5 py-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fas fa-headset text-[9px]"></i>
                                            <span>Call via Web Dialer ↗</span>
                                        </button>
                                        <a href="https://{{ $epbxHost }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-[10.5px] px-2 py-1 rounded-lg transition-colors">
                                            <span>ePBX Portal ↗</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="callStatus === 'error'">
                        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2 shadow-2xs">
                            <i class="fas fa-circle-xmark text-rose-500 text-sm mt-0.5 flex-shrink-0"></i>
                            <div class="flex-1">
                                <p class="font-bold">Could not dispatch call</p>
                                <p class="text-[10.5px] text-rose-700 mt-0.5" x-text="callMessage"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ══ OPTION 4: QUICK CALL DISPOSITION & NOTES ══ --}}
            <div class="border border-slate-200/80 rounded-2xl p-4 bg-white shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fas fa-clipboard-check text-amber-500"></i>
                        <span>Call Outcome / Quick Disposition</span>
                    </span>
                    <span class="text-[10.5px] text-slate-400">1-Click Save to Notes</span>
                </div>

                <div class="flex flex-wrap gap-1.5 mb-2.5">
                    <button type="button" @click="saveQuickDisposition('✅ Customer confirmed order over phone')"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer">
                        <span>✅ Confirmed</span>
                    </button>
                    <button type="button" @click="saveQuickDisposition('📞 Customer requested call back later')"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition-colors cursor-pointer">
                        <span>📞 Call Later</span>
                    </button>
                    <button type="button" @click="saveQuickDisposition('📵 Did not pick up / busy')"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors cursor-pointer">
                        <span>📵 No Answer</span>
                    </button>
                    <button type="button" @click="saveQuickDisposition('📴 Phone switched off / unreachable')"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100 transition-colors cursor-pointer">
                        <span>📴 Switched Off</span>
                    </button>
                    <button type="button" @click="saveQuickDisposition('❌ Customer declined / order cancelled')"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors cursor-pointer">
                        <span>❌ Cancelled</span>
                    </button>
                </div>

                <div class="flex gap-2">
                    <input type="text" x-model="customNote"
                           @keydown.enter.prevent="saveCustomNote()"
                           placeholder="Enter customer call notes or conversation details..."
                           class="flex-1 text-xs px-3 py-2 border border-slate-200 rounded-xl text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                    <button type="button" @click="saveCustomNote()"
                            :disabled="savingNote || !customNote.trim()"
                            class="bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-paper-plane text-[10px]" :class="{ 'fa-spin fa-spinner': savingNote }"></i>
                        <span x-text="savingNote ? 'Saving...' : 'Save'"></span>
                    </button>
                </div>

                <p x-show="noteFeedback" x-cloak
                   class="text-[11px] text-emerald-600 font-semibold mt-2 flex items-center gap-1">
                    <i class="fas fa-check-circle"></i>
                    <span x-text="noteFeedback"></span>
                </p>
            </div>

        </div>

        {{-- ── MODAL FOOTER ── --}}
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="font-mono text-[11px] text-slate-600">IP Telephony: 096XX Cloud PBX</span>
            </div>
            <button type="button" @click="close()"
                    class="px-4 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold text-xs transition-colors cursor-pointer">
                Close
            </button>
        </div>

    </div>
</div>

{{-- Audio element for WebRTC sound --}}
<audio id="epbxRemoteAudio" autoplay></audio>

@push('scripts')
{{-- SIP.js CDN for In-Browser WebRTC calling --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/sip.js/0.20.0/sip.min.js"></script>
<script>
window.openEpbxCallModal = function(data) {
    window.dispatchEvent(new CustomEvent('open-epbx-modal', { detail: data }));
};

function epbxCallCenter() {
    return {
        isOpen: false,
        saleId: null,
        invoiceNo: '',
        customerName: '',
        customerPhone: '',
        orderAmount: '0',

        // In-Browser WebRTC state
        showWebrtcSettings: false,
        webrtcExtension: localStorage.getItem('epbx_webrtc_ext') || '',
        webrtcPassword: localStorage.getItem('epbx_webrtc_pass') || '',
        webrtcRealm: '{{ $epbxHost }}',
        webrtcConnected: false,
        webrtcCalling: false,
        webrtcInCall: false,
        webrtcStatus: '',
        userAgent: null,
        currentSession: null,

        // Calling & notes state
        isCalling: false,
        callStatus: null,
        callMessage: '',
        customNote: '',
        savingNote: false,
        noteFeedback: '',

        open(data) {
            this.saleId        = data.saleId || data.id || null;
            this.invoiceNo     = data.invoice || data.invoice_no || '';
            this.customerName  = data.customer || data.customer_name || 'Customer';
            this.customerPhone = data.phone || data.customer_phone || '';
            this.orderAmount   = data.amount || '0';

            this.callStatus    = null;
            this.callMessage   = '';
            this.customNote    = '';
            this.noteFeedback  = '';
            this.webrtcStatus  = '';
            this.webrtcCalling = false;
            this.isOpen        = true;

            // If WebRTC credentials are saved, connect in background
            if (this.webrtcExtension && this.webrtcPassword && !this.userAgent) {
                this.connectWebRtc();
            }
        },

        close() {
            this.isOpen = false;
        },

        copyPhone() {
            if (!this.customerPhone) return;
            navigator.clipboard.writeText(this.customerPhone).then(() => {
                if (typeof showToast === 'function') {
                    showToast('Phone number copied: ' + this.customerPhone, 'info');
                } else {
                    this.noteFeedback = 'Phone number copied!';
                    setTimeout(() => this.noteFeedback = '', 2500);
                }
            });
        },

        /**
         * Connect to ePBX WebRTC via SIP.js
         */
        async connectWebRtc() {
            if (!this.webrtcExtension || !this.webrtcPassword) {
                return false;
            }

            if (typeof SIP === 'undefined') {
                this.webrtcStatus = 'SIP.js library not loaded. Please refresh the page.';
                return false;
            }

            const cleanExt  = (this.webrtcExtension || '').trim();
            const cleanPass = (this.webrtcPassword || '').trim();
            const wssServer = "wss://" + this.webrtcRealm + "/ws";
            const username  = cleanExt;

            try {
                const uri = SIP.UserAgent.makeURI("sip:" + username + "@" + this.webrtcRealm);
                if (!uri) {
                    this.webrtcStatus = 'Invalid extension number.';
                    return false;
                }

                if (this.userAgent) {
                    try { await this.userAgent.stop(); } catch(e) {}
                    this.userAgent = null;
                }

                const userAgentOptions = {
                    authorizationPassword: cleanPass,
                    authorizationUsername: cleanExt,
                    uri: uri,
                    transportOptions: {
                        server: wssServer,
                        keepAliveInterval: 20,
                    },
                    register: true,
                };

                this.userAgent = new SIP.UserAgent(userAgentOptions);
                this.userAgent.delegate = {
                    onConnect: () => {
                        this.webrtcConnected = true;
                        this.webrtcStatus = 'Connected to PBX';
                    },
                    onDisconnect: (error) => {
                        this.webrtcConnected = false;
                        this.webrtcStatus = error ? 'Disconnected: ' + (error.message || 'PBX WebSocket disconnected') : 'Disconnected';
                    },
                };

                await this.userAgent.start();
                const registerer = new SIP.Registerer(this.userAgent);
                registerer.stateChange.addListener((newState) => {
                    if (newState === SIP.RegistererState.Registered) {
                        this.webrtcConnected = true;
                        this.webrtcStatus = 'Registered (Ready to Call)';
                    }
                });
                await registerer.register();
                return true;
            } catch (e) {
                this.webrtcConnected = false;
                this.userAgent = null;
                this.webrtcStatus = 'WebRTC connection failed (' + (e.message || 'Error') + '). Please use ePBX Web Dialer above.';
                return false;
            }
        },

        saveWebrtcCredentials() {
            localStorage.setItem('epbx_webrtc_ext', (this.webrtcExtension || '').trim());
            localStorage.setItem('epbx_webrtc_pass', (this.webrtcPassword || '').trim());
            this.showWebrtcSettings = false;
            this.webrtcStatus = 'Credentials saved, connecting to PBX...';
            this.connectWebRtc();
        },

        /**
         * Call directly inside browser via WebRTC (No external app needed!)
         */
        async callViaWebRtc() {
            if (!this.customerPhone) return;

            // 1. Check if extension & password are provided
            if (!this.webrtcExtension || !this.webrtcPassword) {
                this.showWebrtcSettings = true;
                this.webrtcStatus = 'Enter Extension & Password for WebRTC, or use ePBX Web Dialer above.';
                return;
            }

            // 2. Connect if not yet connected
            if (!this.userAgent || !this.webrtcConnected) {
                this.webrtcCalling = true;
                this.webrtcStatus = 'Connecting to PBX server...';
                const connected = await this.connectWebRtc();
                if (!connected || !this.userAgent) {
                    this.webrtcCalling = false;
                    return;
                }
            }

            // 3. Guaranteed safety check: NEVER call SIP.Inviter on null userAgent
            if (!this.userAgent) {
                this.webrtcCalling = false;
                this.webrtcStatus = 'PBX connection inactive. Use ePBX Web Dialer above.';
                return;
            }

            const cleanPhone = this.customerPhone.replace(/[^\d+]/g, '');
            const targetURI = SIP.UserAgent.makeURI("sip:" + cleanPhone + "@" + this.webrtcRealm);
            if (!targetURI) {
                this.webrtcCalling = false;
                this.webrtcStatus = 'Invalid phone number: ' + cleanPhone;
                return;
            }

            this.webrtcCalling = true;
            this.webrtcStatus = 'Dialing: ' + cleanPhone + '...';

            try {
                const inviter = new SIP.Inviter(this.userAgent, targetURI, {
                    sessionDescriptionHandlerOptions: {
                        constraints: { audio: true, video: false }
                    }
                });

                inviter.stateChange.addListener((newState) => {
                    if (newState === SIP.SessionState.Established) {
                        this.webrtcInCall = true;
                        this.webrtcCalling = false;
                        this.webrtcStatus = 'Connected! Speaking...';
                        
                        // Setup remote audio stream
                        const audio = document.getElementById('epbxRemoteAudio');
                        const pc = inviter.sessionDescriptionHandler.peerConnection;
                        pc.getReceivers().forEach(receiver => {
                            if (receiver.track && audio) {
                                audio.srcObject = new MediaStream([receiver.track]);
                                audio.play();
                            }
                        });
                    } else if (newState === SIP.SessionState.Terminated) {
                        this.webrtcInCall = false;
                        this.webrtcCalling = false;
                        this.webrtcStatus = 'Call Ended';
                        this.currentSession = null;
                    }
                });

                await inviter.invite();
                this.currentSession = inviter;
            } catch (err) {
                this.webrtcCalling = false;
                this.webrtcStatus = 'Call failed: ' + (err.message || 'Unknown') + '. Use ePBX Web Dialer above.';
            }
        },

        hangupWebRtc() {
            if (this.currentSession) {
                try {
                    this.currentSession.bye();
                } catch(e) {}
                this.currentSession = null;
            }
            this.webrtcInCall = false;
            this.webrtcCalling = false;
            this.webrtcStatus = 'Call Hangup';
        },

        getCleanPhone() {
            let phone = (this.customerPhone || '').replace(/[^\d]/g, '');
            if (phone.startsWith('880') && phone.length === 13) {
                phone = '0' + phone.substring(3);
            }
            return phone;
        },

        /**
         * Trigger external softphone (Only when explicitly clicked by user!)
         */
        dialViaSoftphone(protocol = 'sip') {
            const cleanPhone = this.getCleanPhone();
            if (!cleanPhone) return;
            const targetUrl = protocol + ':' + cleanPhone;

            const a = document.createElement('a');
            a.href = targetUrl;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                if (a.parentNode) a.parentNode.removeChild(a);
            }, 600);

            if (typeof showToast === 'function') {
                showToast(`${protocol.toUpperCase()} dial request sent: ${cleanPhone}`, 'info');
            }
        },

        /**
         * Open ePBX Web softphone dialer popup
         */
        openPcWebDialer() {
            const cleanPhone = this.getCleanPhone();
            if (!cleanPhone) return;
            const dialerBase = '{{ $epbxDialer }}';
            const separator  = dialerBase.includes('?') ? '&' : '?';
            const dialerUrl  = `${dialerBase}${separator}phone=${encodeURIComponent(cleanPhone)}&number=${encodeURIComponent(cleanPhone)}`;

            const width  = 480;
            const height = 740;
            const left   = Math.max(0, (window.screen.width - width) / 2);
            const top    = Math.max(0, (window.screen.height - height) / 2);

            const popup = window.open(
                dialerUrl,
                'epbx_dialer_window',
                `width=${width},height=${height},top=${top},left=${left},status=no,toolbar=no,menubar=no,location=no,resizable=yes,scrollbars=yes`
            );

            if (popup) {
                popup.focus();
                this.webrtcStatus = 'ePBX Web Dialer opened (Number: ' + cleanPhone + ')';
            } else {
                window.open(dialerUrl, '_blank');
            }
        },

        /**
         * Send automated AI voice order verification call via /api/v1/calls/verify
         */
        async sendAiVerificationCall() {
            if (!this.saleId) return;

            this.isCalling    = true;
            this.callStatus   = null;
            this.callMessage  = '';

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name=csrf-token]')?.content;
                const url  = `/admin/sales/${this.saleId}/epbx-verify-call`;

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        phone: this.customerPhone
                    })
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && data.success) {
                    this.callStatus  = 'success';
                    this.callMessage = data.message || 'AI Voice Verification Call sent successfully!';
                    if (typeof showToast === 'function') {
                        showToast(this.callMessage, 'success');
                    }
                } else if (res.status === 402 || data.error_type === 'insufficient_balance') {
                    this.callStatus  = 'insufficient_balance';
                    this.callMessage = data.message || 'Insufficient balance in ePBX wallet. Please recharge at {{ $epbxHost }}.';
                } else if (res.status === 503 || data.error_type === 'tts_unavailable' || (data.message && data.message.toLowerCase().includes('text-to-speech')) || (data.error && data.error.toLowerCase().includes('text-to-speech'))) {
                    this.callStatus  = 'tts_unavailable';
                    this.callMessage = data.message || data.error || 'AI Text-to-Speech service unavailable. Fee refunded.';
                } else {
                    this.callStatus  = 'error';
                    this.callMessage = data.message || data.error || 'Could not dispatch call.';
                }
            } catch (err) {
                this.callStatus  = 'error';
                this.callMessage = 'Server connection error: ' + err.message;
            } finally {
                this.isCalling = false;
            }
        },

        async saveQuickDisposition(text) {
            await this.submitNoteToOrder(text);
        },

        async saveCustomNote() {
            if (!this.customNote.trim()) return;
            const text = this.customNote.trim();
            const saved = await this.submitNoteToOrder(text);
            if (saved) {
                this.customNote = '';
            }
        },

        async submitNoteToOrder(noteText) {
            if (!this.saleId || !noteText) return false;
            this.savingNote   = true;
            this.noteFeedback = '';

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name=csrf-token]')?.content;
                const url  = `/admin/sales/${this.saleId}/epbx-call-note`;

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ note: noteText })
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && data.success) {
                    this.noteFeedback = 'Note saved successfully!';
                    if (typeof showToast === 'function') {
                        showToast('Note saved: ' + noteText, 'success');
                    }

                    const badge = document.getElementById('note-badge-' + this.saleId);
                    if (badge && data.count) {
                        badge.textContent = data.count;
                        badge.classList.remove('hidden');
                    }

                    setTimeout(() => { this.noteFeedback = ''; }, 3000);
                    return true;
                } else {
                    alert(data.message || 'Failed to save note.');
                    return false;
                }
            } catch (e) {
                alert('Server error while saving note!');
                return false;
            } finally {
                this.savingNote = false;
            }
        }
    };
}
</script>
@endpush
