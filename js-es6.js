import $ from 'jquery';

export default class {
	constructor(id) {
		this.selectors = {
			accordion: '.js-accordion',
			wrap: '.js-accordion-wrap',
			content: '.js-accordion-content',
			contentWrap: '.js-accordion-content-wrap'
		};

		this.classes = {
			active: 'accordion__wrap_active',
			open: 'accordion__content_open'
		};
		this.$el = $('#'+id);
		this.elems = [];
	}

	get({wrap, content}) {
		this.elems = this.$el.toArray().map(el => {
			const $el = $(el);
			return {
				$el,
				items: $el.find(wrap).toArray().map(elem => {
					return {
						$wrap: $(elem),
						content: {
							$el: $(elem).next(content)
						}
					};
				})
			};
		});

		return this;
	}

	open({$wrap, content: {$el}}, {active, open}) {
		return () => {
			const {contentWrap} = this.selectors;
			const height = $el.find(contentWrap).outerHeight(true);
			if (!$wrap.hasClass(active)) {
				$el.height(height + 10 + 'px');
				$wrap.addClass(active);
				$el.addClass(open);
			}else {
				$el.height(0);
				$el.attr('style', '');
				$wrap.removeClass(active);
				$el.removeClass(open);
			}
		};
	}

	init() {
		this.elems.forEach(elem => {
			elem.items.forEach(item => {
				item.$wrap.on('click', this.open(item, this.classes));
			});
		});

		$(window).resize(() => {
			$('.' + this.classes.open).attr('style', '').height('');
			const {contentWrap} = this.selectors;

			$('.' + this.classes.open).each(function () {
				const height = $(this).find(contentWrap).outerHeight(true);
				$(this).height(height+10);
			});
		});

		// По умолчанию все табы раскрыты
		this.openall();

		return this;
	}

	openall() {
		this.elems.forEach(elem => {
			elem.items.forEach(item => {
				this.open(item, this.classes)();
			});
		});
	}

	render() {
		if (this.$el.length) {
			this.
				get(this.selectors)
				.init();
		}
	}
}
