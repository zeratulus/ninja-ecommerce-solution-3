class CustomItem {
    options = {}

    fillOptions(options) {
        for (const [key, value] of Object.entries(options)) {
            this.options[key] = value;
        }
    }

    /**
     * Fill only that options, that are needed in class
     * @param options
     * @param required Array with keys that required
     */
    fillOptionsWithRequired(options, required) {
        for (const [key, value] of Object.entries(options)) {
            if (required.includes(key)) {
                this.options[key] = value;
            }
        }
    }

    randomize(min, max) {
        return Math.floor(Math.random() * (max - min) + min);
    }
}

class ProcessorRotate extends CustomItem {
    options = {
        rotateFactor: 30, //percents 0 - 100
        isRotatable: false,
    }

    availableOptions = ['rotateFactor', 'isRotatable'];

    constructor(options) {
        super();
        this.fillOptionsWithRequired(options, this.availableOptions);
    }

    process() {
        //rotating routine
        if (this.options.isRotatable) {
            let action = this.randomize(0, 10), rotation;
            if (action % 2) {
                rotation = 0 - (this.randomize(0, this.options.rotateFactor));
            } else {
                rotation = this.randomize(0, this.options.rotateFactor);
            }
            return `rotate(${rotation}deg)`;
        } else {
            return '';
        }
    }
}

class ProcessorScale extends CustomItem {
    options = {
        scaleFactor: 30, //percents 0 - 100
        isScalable: false,
    }

    availableOptions = ['scaleFactor', 'isScalable'];

    constructor(options) {
        super();
        this.fillOptionsWithRequired(options, this.availableOptions);
    }

    process() {
        //scaling routine
        if (this.options.isScalable) {
            let action = this.randomize(0, 10), scale;
            if (action % 2) {
                scale = 1 - (this.randomize(0, this.options.scaleFactor) / 100);
            } else {
                scale = 1 + (this.randomize(0, this.options.scaleFactor) / 100);
            }
            return `scale(${scale})`;
        } else {
            return '';
        }
    }
}

class ProcessorOpacity extends CustomItem {
    options = {
        // opacityFactor: '30-60', //percents 0 - 100
    }

    availableOptions = ['opacityFactor', 'opacity'];

    constructor(options) {
        super();
        this.fillOptionsWithRequired(options, this.availableOptions);
    }

    process() {
        let result = '';

        if (this.options.hasOwnProperty('opacityFactor')) {
            if (this.options.opacityFactor.toString().indexOf('-')) {
                let range = this.options.opacityFactor.split('-');
                let opacity = this.randomize(parseInt(range[0]), parseInt(range[1])) / 100;
                result = `opacity: ${opacity};`;
            } else {
                let opacity = this.randomize(0, parseInt(this.options.opacityFactor)) / 100;
                result = `opacity: ${opacity};`;
            }
        } else if (this.options.hasOwnProperty('opacity')) {
            result = `opacity: ${this.options.opacity};`;
        }

        return result;
    }
}

// class

class ProcessorAnimate {

}

class BgItem extends CustomItem {
    options = {};

    constructor(element, options) {
        super();
        this.fillOptions(options);
        this.elementDeclaration = element;
        this.scale = new ProcessorScale(options);
        this.rotate = new ProcessorRotate(options);
    }

    stringToElement(DomParser, string) {
        return DomParser.parseFromString(string, 'text/html').body.childNodes[0];
    }

    render(DomParser, parentEl) {
        let el;
        switch (typeof this.elementDeclaration) {
            case "string":
                el = this.stringToElement(DomParser, this.elementDeclaration);
                break;
            default:
                return false;
            //TODO: if typeof HTMLElement
            // el = this.elementDeclaration
            // break;
        }

        //set position
        let left, top;
        left = this.randomize(0, parentEl.clientWidth);
        if (left + el.clientWidth > parentEl.clientWidth) {
            left = left - (parentEl.clientWidth - left - el.clientWidth);
        }
        top = this.randomize(0, parentEl.clientHeight);
        if (top + el.clientHeight >= parentEl.clientHeight) {
            top -= el.clientHeight;
        }

        let cssText = `position: absolute; left: ${left}px; top: ${top}px;`;
        //todo: Processors list... auto adding processors by options declaration and auto processing... without calling someProcessor.process() manually
        const scaleText = this.scale.process();
        const rotateText = this.rotate.process();
        if (this.scale.options.isScalable || this.rotate.options.isRotatable) {
            cssText += ` transform: ${scaleText} ${rotateText};`;
        }

        if (this.options.hasOwnProperty('opacity') || this.options.hasOwnProperty('opacityFactor')) {
            this.opacity = new ProcessorOpacity(this.options);
            cssText += this.opacity.process();
        }

        // console.log(this.element, cssText);
        el.style.cssText = cssText;
        return el;
    }
}

class BackgroundRandomizer extends CustomItem {

    options = {
        areaExcludeXStart: 100,
        areaExcludeXEnd: screen.width - 100,
        zIndex: 0,
        bgItemsCount: 30,
        bgElement: 'body',
        additionalClasses: 'fadeIn',

        //TODO: remove...
        scaleFactor: 30, //percents 0 - 100
        isScaling: true,
        rotateFactor: 30, //percents 0 - 100
        isRotating: true
    }

    parentEl = document.querySelector(this.options.bgElement ?? 'body');

    DomParser = new DOMParser();

    /**
     * Array of elements that we will put to canvas
     * @type {[]}
     */
    //TODO: check intersection with already put elements
    elementsToPut = []

    /**
     * Array of elements that already put to canvas
     * @type {[]}
     */
    bgItems = []

    constructor(options) {
        super();
        this.fillOptions(options);
        this.parentEl = document.querySelector(this.options.bgElement ?? 'body');
    }

    addBgItemToPut(el) {
        this.elementsToPut.push(el);
    }

    stringToElement(string) {
        return this.DomParser.parseFromString(string, 'text/html').body.childNodes[0];
    }

    getRandomElement() {
        let idx = this.randomize(0, this.elementsToPut.length);
        let item = this.elementsToPut[idx];
        return item;
    }

    render() {
        for (let i = 0; i < this.options.bgItemsCount; i++) {
            let item = this.getRandomElement();
            // console.log(item);
            let el = item.render(this.DomParser, this.parentEl);
            // console.log(el);
            if (el) {
                el.classList.add(this.options.additionalClasses);
                this.bgItems.push(el);
                this.parentEl.append(el);
            }
        }
        // for (const item in this.bgItems) {
        //     parent.append(item);
        // }
    }
}

//TODO: jQuery calls
// $.fn.bgRandomizer = function (options) {
//     let bgRandomizer = new BackgroundRandomizer(options);
// };
