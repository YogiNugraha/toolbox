// AlpineJS Plugins
import persist from "@alpinejs/persist"; // @see https://alpinejs.dev/plugins/persist
import collapse from "@alpinejs/collapse"; // @see https://alpinejs.dev/plugins/collapse
import intersect from "@alpinejs/intersect"; // @see https://alpinejs.dev/plugins/intersect

// Third Party Libraries

/*
    Scrollbar Library
    @see https://github.com/Grsmto/simplebar
*/
import SimpleBar from "simplebar";

/*
    Code highlighting library
    Just for demo purpose only for highlighting code
    @see https://highlightjs.org/
*/
import hljs from "highlight.js/lib/core";
import xml from "highlight.js/lib/languages/xml";

/*
    Date Utility Library
    @see https://day.js.org/
*/
import dayjs from "dayjs";

/*
    Carousel Library
    @see https://swiperjs.com/
*/
import Swiper from "swiper/bundle";

/*
    Drag & Drop Library
    @see https://github.com/SortableJS/Sortable
*/
import Sortable from "sortablejs";

/*
    Charts Libraries
    @see https://apexcharts.com/
*/
import ApexCharts from "apexcharts";

/*
    Tables Libraries
    @see https://gridjs.io/
*/
import * as Gridjs from "gridjs";

//  Forms Libraries
import "@caneara/iodine"; // @see https://github.com/caneara/iodine
import * as FilePond from "filepond"; // @see https://pqina.nl/filepond/
import FilePondPluginImagePreview from "filepond-plugin-image-preview"; // @see https://pqina.nl/filepond/docs/api/plugins/image-preview/
import Quill from "quill"; // @see https://quilljs.com/
import flatpickr from "flatpickr"; // @see https://flatpickr.js.org/
import Tom from "tom-select/dist/js/tom-select.complete.min"; // @see https://tom-select.js.org/

// Import Fortawesome icons
import "@fortawesome/fontawesome-free/css/all.css";

// Helper Functions
import * as helpers from "./utils/helpers";

// Pages Scripts
import * as pages from "./pages";

// Global Store
import store from "./store";

// Breakpoints Store
import breakpoints from "./utils/breakpoints";

// Alpine Components
import usePopper from "./components/usePopper";
import accordionItem from "./components/accordionItem";

// Alpine Directives
import tooltip from "./directives/tooltip";
import inputMask from "./directives/inputMask";

// Alpine Magic Functions
import notification from "./magics/notification";
import clipboard from "./magics/clipboard";

// Register HTML, XML language for highlight.js
// Just for demo purpose only for highlighting code
hljs.registerLanguage("xml", xml);
hljs.configure({ ignoreUnescapedHTML: true });

// Register plugin image preview for filepond
FilePond.registerPlugin(FilePondPluginImagePreview);

window.hljs = hljs;
window.dayjs = dayjs;
window.SimpleBar = SimpleBar;
window.Swiper = Swiper;
window.Sortable = Sortable;
window.ApexCharts = ApexCharts;
window.Gridjs = Gridjs;
window.FilePond = FilePond;
window.flatpickr = flatpickr;
window.Quill = Quill;
window.Tom = Tom;

window.helpers = helpers;
window.pages = pages;

// Preloader fallback to prevent stuck loader
const dismissPreloader = () => {
    const preloader = document.querySelector(".app-preloader");
    if (preloader) {
        setTimeout(() => {
            preloader.classList.add("animate-[cubic-bezier(0.4,0,0.2,1)_fade-out_500ms_forwards]");
            setTimeout(() => preloader.remove(), 1000);
        }, 150);
    }
};

if (document.readyState === "complete") {
    dismissPreloader();
} else {
    window.addEventListener("load", dismissPreloader);
}

let isAlpineInitialized = false;
document.addEventListener('alpine:init', () => {
    if (isAlpineInitialized) return;
    isAlpineInitialized = true;

    try {
        if (window.Alpine) {
            if (!window.Alpine.$persist) {
                window.Alpine.plugin(persist);
            }
            if (!window.Alpine.collapse) {
                window.Alpine.plugin(collapse);
            }
            if (!window.Alpine.intersect) {
                window.Alpine.plugin(intersect);
            }
        }
    } catch (e) {
        console.warn('Alpine plugins already registered', e);
    }

    try {
        window.Alpine.directive("tooltip", tooltip);
    } catch (e) { console.warn('tooltip err', e); }

    try {
        window.Alpine.directive("input-mask", inputMask);
    } catch (e) { console.warn('input-mask err', e); }

    try {
        window.Alpine.magic("notification", () => notification);
    } catch (e) { console.warn('notification err', e); }

    try {
        window.Alpine.magic("clipboard", () => clipboard);
    } catch (e) { console.warn('clipboard err', e); }

    try {
        window.Alpine.store("breakpoints", breakpoints);
    } catch (e) { console.warn('breakpoints err', e); }

    try {
        window.Alpine.store("global", store());
    } catch (e) { console.warn('global store err', e); }

    try {
        window.Alpine.store("confirmModal", {
            show: false,
            title: '',
            text: '',
            action: '',
            data: {},
            open(title, text, action, data) {
                this.title = title;
                this.text = text;
                this.action = action;
                this.data = data || {};
                this.show = true;
            },
            close() {
                this.show = false;
            },
            confirm() {
                this.show = false;
                if (this.action === 'submit-form') {
                    document.getElementById(this.data.formId).submit();
                } else {
                    window.Livewire.dispatch(this.action, this.data);
                }
            }
        });
    } catch (e) { console.warn('confirmModal store err', e); }

    try {
        window.Alpine.data("usePopper", usePopper);
    } catch (e) { console.warn('usePopper err', e); }

    try {
        window.Alpine.data("accordionItem", accordionItem);
    } catch (e) { console.warn('accordionItem err', e); }
});
