
class ReadProgress {
    
    element;
    bar;
    strings = {
        minute: 'minute',
        minutes: 'minutes',
        et_label: 'Estimated reading time: ',
    };
    selector = '.entry-content';
    height = '4px';
    color = 'rgb(255, 10, 10)';
    useET = '1';
    wpm = 200;

    constructor() {
        this.setConfig();
        this.addBehaviors();
    }

    setConfig() {
        if (!window.RPConfig) {
            return;
        }
        for (const key in RPConfig) {
            const propName = this.translate(key);
            if (Object.prototype.hasOwnProperty.call(this, propName)) {
                this[propName] = RPConfig[key];
            }
        }
    }
    
    addBehaviors() {
        document.addEventListener('DOMContentLoaded', () => {
            this.getElement();
            if (this.element) {
                this.addHTML();
                window.addEventListener('scroll', () => this.autoWidth());
                window.addEventListener('resize', () => this.autoWidth());
                this.autoWidth();
            }
        });
    }
    
    getScrollInfo() {
        const rect = this.element.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const d = document, dd = d.documentElement;
        const scroll = window.pageYOffset || dd.scrollTop || d.body.scrollTop;
        const ret = {
            start: rect.top + scroll,
            end: rect.bottom + scroll - windowHeight,
            scroll
        };
        return ret;
    }
    
    autoWidth() {
        let percent;
        const info = this.getScrollInfo();
        if (info.scroll < info.start) {
            percent = 0;
        } else if(info.scroll > info.end) {
            percent = 100;
        } else {
            const calculatePercentage = (value, min, max) => {
                if (value <= min) return 0;
                if (value >= max) return 100;
                return ((value - min) / (max - min)) * 100;
            };
            percent = calculatePercentage(info.scroll, info.start, info.end);
        }
        this.bar.style.width = `${percent}%`;
    }

    addHTML() {
        this.trail = document.createElement('div');
        this.trail.className = 'rp-trail';
        this.bar = document.createElement('div');
        this.bar.className = 'rp-bar';
        if (this.height) {
            this.bar.style.height = this.height;
        }
        if (this.color) {
            this.bar.style.backgroundColor = this.color;
        }
        this.trail.appendChild(this.bar);
        document.body.appendChild(this.trail);

        if (this.useET === '1') {
            this.getElement();
            if (this.element) {
                const etWrap = document.createElement('div');
                etWrap.className = 'rp-estimated-time';
                etWrap.innerText = this.strings.et_label;
                this.et = document.createElement('span');
                this.et.innerText = this.estimateTime();
                etWrap.appendChild(this.et);
                this.element.parentElement.insertBefore(etWrap, this.element);
            }
        }
    }

    getElement() {
        if (!this.element) {
            this.element = document.querySelector(this.selector);
        }
        return this.element || false;
    }

    estimateTime() {
        if (!this.getElement()) {
            return 'undefined';
        }
        const words = this.element.innerText.trim().split(/\s*/).length;
        const minutes = Math.ceil(words / this.wpm);
        const minStr = minutes == 1 ? this.strings.minute : this.strings.minutes;
        return `${minutes} ${minStr}`;
    }

    translate(varName) {
        if (/_/.test(varName) && !/[A-Z]/.test(varName)) {
            varName = varName.replace(/_([a-z])/g, (_, letra) => letra.toUpperCase());
        }
        else if (!/_/.test(varName) && /[A-Z]/.test(varName)) {
            varName = varName.replace(/([A-Z])/g, (_, letra) => '_' + letra.toLowerCase());
        }
        return varName;
    }
}