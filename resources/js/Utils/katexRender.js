import katex from 'katex';

/**
 * Format text containing math formulas ($...$ or $$...$$) into rendered KaTeX HTML
 */
export function renderMathInText(text) {
    if (!text || typeof text !== 'string') return '';

    // First replace block math $$...$$
    let output = text.replace(/\$\$([\s\S]+?)\$\$/g, (match, formula) => {
        try {
            return katex.renderToString(formula.trim(), {
                displayMode: true,
                throwOnError: false,
            });
        } catch (e) {
            return match;
        }
    });

    // Then replace inline math $...$
    output = output.replace(/\$([^\$\n]+?)\$/g, (match, formula) => {
        try {
            return katex.renderToString(formula.trim(), {
                displayMode: false,
                throwOnError: false,
            });
        } catch (e) {
            return match;
        }
    });

    return output;
}
