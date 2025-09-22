# NS Hamburger Overlay Menu

![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.5+-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-blue)
![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green)

An accessible hamburger overlay menu plugin for WordPress with gradient animations, multi-column layout, and full keyboard navigation support.

> **Note**: This plugin is being prepared for submission to the WordPress.org Plugin Directory and follows all WordPress coding standards and guidelines.

## Features

- ✅ **Accessible**: Full ARIA support, keyboard navigation, focus management
- ✅ **Responsive**: Works on all screen sizes with optimized mobile experience
- ✅ **Customizable**: Color schemes, column layouts, typography settings, positioning
- ✅ **Individual Colors**: 5 separate color controls for hamburger icon lines
- ✅ **Smart Positioning**: Custom positioning with browser-width responsive adjustments
- ✅ **Block Editor**: Native Gutenberg block with live preview
- ✅ **Performance**: Lightweight CSS/JS, no jQuery dependency
- ✅ **i18n Ready**: Full translation support with included POT file

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later

## Quick Start

1. **Install the plugin**: Upload and activate through WordPress admin
2. **Set up navigation**:
   - **Classic themes**: Go to Appearance → Menus, assign menu to "Hamburger Overlay Menu" location
   - **Block themes**: Create a Navigation block in the block editor (auto-detected)
   - **No setup needed**: Plugin will automatically use existing navigation or generate page list
3. **Configure settings**: Visit Settings → NS Hamburger Menu to customize appearance
4. **Add to pages**: Use auto-insert or add the block manually in Gutenberg

## Usage

### Auto-Insert Mode
Enable "Auto Insert" in settings to display on all pages automatically.

### Manual Placement
Use the Gutenberg block `NS Hamburger Menu` or shortcode `[ns_hamburger_menu]`.

### Theme Integration
```php
// Add to your theme templates
if (function_exists('nshm_display_menu')) {
    nshm_display_menu();
}
```

## Customization

### Color Schemes & Design Presets
- **Color Presets**: Choose from built-in color schemes (Blue, Green, Red, Orange, Black) or set custom gradient colors
- **Design Presets**: Select from predefined visual styles (Normal, Pattern 1-3) with unique animations and effects
- **Custom CSS**: Add additional styling for fine-tuned customization

### Layout Options
- **Columns**: 1-6 column grid layout
- **Typography**: Separate font sizes for parent/child menu items
- **Animation**: Optional hue rotation animation with speed control

### Position & Icon Customization
- **Position Modes**:
  - Default positions (top-left, top-right)
  - Custom positioning with screen-based coordinates
- **Responsive Positioning**: Automatic adjustment to prevent off-screen display
- **Individual Icon Colors**: 5 separate color controls
  - Closed state: top, middle, bottom lines
  - Open state: two diagonal lines of × mark
- **WordPress Color Picker**: Consistent UI with color presets and custom hex values

### Block Slots
Add custom content above/below the menu using slot blocks within the Gutenberg block.

## Accessibility Features

- ARIA labels and states for screen readers
- Keyboard navigation (Tab, Shift+Tab, Enter, Space, Escape)
- Focus trapping within open menu
- Focus restoration when menu closes
- Respects `prefers-reduced-motion`

## Developer

### Hooks & Filters
```php
// Customize menu markup
add_filter('nshm_menu_args', function($args) {
    $args['depth'] = 3;
    return $args;
});

// Modify CSS variables
add_filter('nshm_css_vars', function($vars) {
    $vars['--ns-z'] = 99999;
    return $vars;
});
```

### Template Override
Copy `/templates/hamburger-menu.php` to your theme's `/ns-hamburger-menu/` folder to customize markup.

## FAQ

**Q: Can I use this with any theme?**
A: Yes, works with both classic and block themes. Block themes are fully supported with automatic navigation detection.

**Q: Does it work on mobile?**
A: Yes, optimized for touch devices with proper spacing and improved animations.

**Q: How does it work with block themes?**
A: Automatically detects Navigation blocks from your site. If no traditional menu is assigned, it will use existing Navigation blocks or generate a page list.

**Q: Can I translate the interface?**
A: Yes, uses standard WordPress i18n with included POT file.

**Q: Is it accessible?**
A: Yes, follows WCAG guidelines with full keyboard and screen reader support.

## Support

For issues and feature requests, please use the [GitHub repository](https://github.com/netservice/ns-hamburger-menu).

Once available on WordPress.org, you can also find support through the official plugin page and community forums.

## License

GPL-2.0-or-later. See LICENSE file for details.

---

# NS Hamburger Overlay Menu (日本語)

![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.5+-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-blue)
![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green)

グラデーションアニメーション、マルチカラムレイアウト、完全なキーボードナビゲーションサポートを備えた、アクセシブルなハンバーガーオーバーレイメニュープラグインです。

## 特徴

- ✅ **アクセシブル**: 完全なARIAサポート、キーボードナビゲーション、フォーカス管理
- ✅ **レスポンシブ**: すべての画面サイズに対応、モバイル体験も最適化
- ✅ **カスタマイズ可能**: カラースキーム、カラムレイアウト、タイポグラフィ設定
- ✅ **ブロックエディター**: ライブプレビュー付きネイティブGutenbergブロック
- ✅ **パフォーマンス**: 軽量なCSS/JS、jQuery非依存
- ✅ **i18n対応**: POTファイル付きの完全な翻訳サポート

## 必要環境

- WordPress 6.5以降
- PHP 7.4以降

## クイックスタート

1. **プラグインをインストール**: WordPress管理画面からアップロード・有効化
2. **ナビゲーション設定**:
   - **クラシックテーマ**: 外観→メニューで「Hamburger Overlay Menu」の場所にメニューを割り当て
   - **ブロックテーマ**: ナビゲーションブロックを作成（自動検出されます）
   - **設定不要**: 既存のナビゲーションを自動使用、またはページ一覧を生成
3. **設定を調整**: 設定→NS Hamburger Menuで外観をカスタマイズ
4. **ページに追加**: 自動挿入を有効にするか、Gutenbergで手動配置

## 使用方法

### ナビゲーション設定詳細
- **クラシックテーマ**: 外観→メニューで「Hamburger Overlay Menu」の場所にメニューを割り当て
- **ブロックテーマ**: ナビゲーションブロックを作成（自動検出されます）
- **自動フォールバック**: ナビゲーションが未設定の場合、ページ一覧を自動生成

### 自動挿入モード
設定で「自動挿入」を有効にすると、全ページに自動表示されます。

### 手動配置
Gutenbergブロック「NS Hamburger Menu」またはショートコード`[ns_hamburger_menu]`を使用します。

### テーマ統合
```php
// テーマテンプレートに追加
if (function_exists('nshm_display_menu')) {
    nshm_display_menu();
}
```

## カスタマイズ

### カラースキーム・デザインプリセット
- **カラープリセット**: 内蔵カラースキーム（ブルー、グリーン、レッド、オレンジ、ブラック）から選択するか、カスタムグラデーションカラーを設定
- **デザインプリセット**: 定義済みビジュアルスタイル（ノーマル、パターン1〜3）から選択、独自のアニメーションや効果を適用
- **カスタムCSS**: 細かなカスタマイズ用の追加スタイリング機能

### レイアウトオプション
- **カラム数**: 1〜6カラムのグリッドレイアウト
- **タイポグラフィ**: 親・子メニュー項目の個別フォントサイズ
- **アニメーション**: 速度調整可能な色相回転アニメーション

### ブロックスロット
Gutenbergブロック内のスロットブロックを使用して、メニューの上下にカスタムコンテンツを追加できます。

## アクセシビリティ機能

- スクリーンリーダー向けARIAラベル・状態
- キーボードナビゲーション（Tab、Shift+Tab、Enter、Space、Escape）
- 開いたメニュー内でのフォーカストラップ
- メニューを閉じる際のフォーカス復帰
- `prefers-reduced-motion`への対応

## 開発者向け

### フック・フィルター
```php
// メニューマークアップをカスタマイズ
add_filter('nshm_menu_args', function($args) {
    $args['depth'] = 3;
    return $args;
});

// CSS変数を変更
add_filter('nshm_css_vars', function($vars) {
    $vars['--ns-z'] = 99999;
    return $vars;
});
```

### テンプレートオーバーライド
`/templates/hamburger-menu.php`をテーマの`/ns-hamburger-menu/`フォルダにコピーしてマークアップをカスタマイズできます。

## よくある質問

**Q: どのテーマでも使用できますか？**  
A: はい、クラシックテーマとブロックテーマの両方で動作します。

**Q: モバイルでも動作しますか？**  
A: はい、適切な間隔を持つタッチデバイス向けに最適化されています。

**Q: インターフェースを翻訳できますか？**  
A: はい、POTファイル付きの標準WordPress i18nを使用しています。

**Q: アクセシブルですか？**  
A: はい、完全なキーボードとスクリーンリーダーサポートでWCAGガイドラインに準拠しています。

## サポート

課題や機能リクエストについては、[GitHubリポジトリ](https://github.com/netservice/ns-hamburger-menu)をご利用ください。

## ライセンス

GPL-2.0-or-later。詳細はLICENSEファイルをご覧ください。