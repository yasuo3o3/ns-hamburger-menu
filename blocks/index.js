( function( wp ) {
  const { registerBlockType } = wp.blocks;
  const { InspectorControls, InnerBlocks } = wp.blockEditor || wp.editor;
  const {
    PanelBody, RangeControl, ToggleControl,
    __experimentalNumberControl: NumberControl,
    TextControl, RadioControl
  } = wp.components;
  const { createElement: h, Fragment } = wp.element;
  const { __ } = wp.i18n || { __: (text) => text };

  // InnerBlocks.Content reference for save functions
  const InnerBlocksContent = (wp.blockEditor || wp.editor).InnerBlocks.Content;

  /* ========== 親: ns/hamburger ========== */
  const Title = () => h('div', { style:{padding:'12px',border:'1px dashed #ccc',borderRadius:'8px',background:'#fafafa'} },
    h('strong', null, 'NS Hamburger Menu'),
    h('div', { style:{marginTop:'6px',opacity:.8} }, __('フロントでは UL の上下にスロット内容が差し込まれます', 'ns-hamburger-menu'))
  );

  registerBlockType('ns/hamburger', {
    title: 'NS Hamburger Menu',
    icon: 'menu',
    category: 'widgets',
    attributes: {
      columns: { type:'number', default: null },
      topFontPx: { type:'number', default: null },
      subFontPx: { type:'number', default: null },
      colorStart: { type:'string', default: null },
      colorEnd: { type:'string', default: null },
      hueAnim: { type:'boolean', default: null },
      hueSpeedSec: { type:'number', default: null },
      zIndex: { type:'number', default: null }
    },
    edit: (props) => {
      const { attributes, setAttributes } = props;
      const setNum = (k) => (v)=> setAttributes({ [k]: (v===""||v===undefined)? null : Number(v) });
      const setStr = (k) => (v)=> setAttributes({ [k]: v || null });

      const template = [
        ['ns/hamburger-slot', { position: 'before' }],
        ['ns/hamburger-slot', { position: 'after' }]
      ];

      return h(Fragment, null,
        h(InspectorControls, null,
          h(PanelBody, { title:__('表示設定（未入力はプラグイン既定を使用）', 'ns-hamburger-menu'), initialOpen:true },
            h(RangeControl, {
              label:__('列数（1〜6）', 'ns-hamburger-menu'), min:1, max:6, allowReset:true, value: attributes.columns,
              onChange: (v)=> setAttributes({ columns: v ?? null })
            }),
            h(NumberControl, { label:__('親の文字サイズ(px)', 'ns-hamburger-menu'), value: attributes.topFontPx, min:10, onChange: setNum('topFontPx') }),
            h(NumberControl, { label:__('子の文字サイズ(px)', 'ns-hamburger-menu'), value: attributes.subFontPx, min:8,  onChange: setNum('subFontPx') }),
            h(TextControl, { label:__('開始色（#0ea5e9 など）', 'ns-hamburger-menu'), value: attributes.colorStart || '', onChange: setStr('colorStart') }),
            h(TextControl, { label:__('終了色（#a78bfa など）', 'ns-hamburger-menu'), value: attributes.colorEnd || '', onChange: setStr('colorEnd') }),
            h(ToggleControl, { label:__('色相アニメON', 'ns-hamburger-menu'), checked: attributes.hueAnim ?? undefined, onChange: (v)=> setAttributes({ hueAnim: v }) }),
            h(NumberControl, { label:__('色相アニメ速度(秒/周)', 'ns-hamburger-menu'), value: attributes.hueSpeedSec, min:3, onChange: setNum('hueSpeedSec') }),
            h(NumberControl, { label:'Z-index', value: attributes.zIndex, min:1000, onChange: setNum('zIndex') })
          )
        ),
        h(Title),
        h('div', { style:{marginTop:'8px',padding:'8px 10px',background:'#fff',border:'1px solid #e5e7eb',borderRadius:'8px'} },
          h('div', { style:{fontSize:12,opacity:.7,marginBottom:6} }, __('Add top/bottom slots here to include custom blocks', 'ns-hamburger-menu')),
          h(InnerBlocks, { allowedBlocks:['ns/hamburger-slot'], template, templateLock:false })
        )
      );
    },
    // 親は「子の内容」を保存する
    save: () => h( InnerBlocksContent )
  });

  /* ========== 子: ns/hamburger-slot（ULの上/下） ========== */
  registerBlockType('ns/hamburger-slot', {
    title: 'NS Hamburger Slot',
    icon: 'insert',
    category: 'widgets',
    parent: ['ns/hamburger'],
    attributes: {
      position: { type:'string', default:'before' } // 'before' | 'after'
    },
    supports: { reusable: false },
    edit: (props) => {
      const { attributes, setAttributes } = props;
      const pos = attributes.position || 'before';
      return h(Fragment, null,
        h(InspectorControls, null,
          h(PanelBody, { title:__('スロット位置', 'ns-hamburger-menu'), initialOpen:true },
            h(RadioControl, {
              label:__('UL との位置', 'ns-hamburger-menu'),
              selected: pos,
              options: [
                { label:__('上（before）', 'ns-hamburger-menu'), value:'before' },
                { label:__('下（after）', 'ns-hamburger-menu'),  value:'after' }
              ],
              onChange: (v)=> setAttributes({ position: v || 'before' })
            })
          )
        ),
        h('div', { style:{padding:'10px',border:'1px dashed #cbd5e1',borderRadius:'8px',background:'#f8fafc'} },
          h('div', { style:{fontSize:12,opacity:.65,marginBottom:6} }, __('この中身が「%s」に出ます', 'ns-hamburger-menu').replace('%s', pos==='after'?__('UL の下', 'ns-hamburger-menu'):__('UL の上', 'ns-hamburger-menu'))),
          h(InnerBlocks)
        )
      );
    },
    // 子は"枠"を保存＝ position 属性をサーバー側に渡す
    save: () => h( InnerBlocksContent )
  });

} )( window.wp );
