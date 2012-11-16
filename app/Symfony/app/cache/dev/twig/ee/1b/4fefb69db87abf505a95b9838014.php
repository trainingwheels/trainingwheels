<?php

/* WebProfilerBundle:Collector:router.html.twig */
class __TwigTemplate_ee1b4fefb69db87abf505a95b9838014 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("WebProfilerBundle:Profiler:layout.html.twig");

        $this->blocks = array(
            'toolbar' => array($this, 'block_toolbar'),
            'menu' => array($this, 'block_menu'),
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "WebProfilerBundle:Profiler:layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
    }

    // line 6
    public function block_menu($context, array $blocks = array())
    {
        // line 7
        echo "<span class=\"label\">
    <span class=\"icon\"><img src=\"";
        // line 8
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("bundles/webprofiler/images/profiler/routing.png"), "html", null, true);
        echo "\" alt=\"Routing\" /></span>
    <strong>Routing</strong>
</span>
";
    }

    // line 13
    public function block_panel($context, array $blocks = array())
    {
        // line 14
        echo "    ";
        echo $this->env->getExtension('actions')->renderAction("WebProfilerBundle:Router:panel", array("token" => $this->getContext($context, "token")), array());
    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Collector:router.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  55 => 16,  32 => 5,  29 => 4,  788 => 469,  785 => 468,  774 => 466,  770 => 465,  766 => 463,  753 => 462,  727 => 457,  724 => 456,  705 => 454,  688 => 453,  684 => 451,  680 => 450,  676 => 449,  672 => 448,  668 => 447,  664 => 446,  661 => 445,  659 => 444,  642 => 443,  631 => 442,  616 => 437,  611 => 435,  607 => 434,  604 => 433,  602 => 432,  588 => 431,  556 => 401,  538 => 398,  521 => 397,  518 => 396,  516 => 395,  511 => 393,  506 => 391,  179 => 87,  171 => 85,  164 => 82,  159 => 80,  154 => 77,  148 => 75,  142 => 73,  124 => 61,  110 => 52,  107 => 51,  26 => 3,  203 => 71,  176 => 86,  174 => 61,  168 => 60,  158 => 59,  130 => 47,  100 => 34,  88 => 41,  79 => 24,  202 => 94,  189 => 70,  183 => 68,  165 => 64,  162 => 63,  151 => 62,  145 => 58,  136 => 55,  132 => 54,  125 => 52,  120 => 51,  93 => 43,  89 => 35,  85 => 40,  82 => 25,  47 => 13,  25 => 3,  75 => 24,  69 => 20,  66 => 30,  60 => 27,  56 => 25,  54 => 13,  42 => 10,  386 => 160,  383 => 159,  377 => 158,  375 => 157,  368 => 156,  364 => 155,  360 => 153,  358 => 152,  355 => 151,  352 => 150,  350 => 149,  342 => 147,  340 => 146,  337 => 145,  328 => 140,  325 => 139,  318 => 135,  312 => 131,  309 => 130,  306 => 129,  304 => 128,  299 => 125,  290 => 120,  287 => 119,  285 => 118,  280 => 115,  278 => 114,  273 => 111,  271 => 110,  266 => 107,  262 => 105,  256 => 103,  252 => 101,  245 => 97,  238 => 93,  232 => 89,  229 => 88,  224 => 86,  219 => 83,  213 => 79,  210 => 78,  207 => 73,  205 => 95,  200 => 73,  194 => 92,  191 => 68,  188 => 89,  186 => 67,  181 => 63,  175 => 59,  172 => 67,  169 => 84,  167 => 56,  160 => 53,  141 => 56,  128 => 53,  105 => 27,  101 => 25,  95 => 23,  86 => 20,  80 => 19,  77 => 35,  74 => 34,  71 => 33,  65 => 23,  59 => 12,  45 => 11,  34 => 5,  68 => 24,  61 => 16,  44 => 12,  37 => 6,  20 => 1,  161 => 81,  153 => 50,  150 => 49,  147 => 48,  143 => 57,  137 => 71,  121 => 35,  118 => 50,  113 => 44,  109 => 38,  106 => 41,  104 => 36,  99 => 45,  96 => 32,  94 => 31,  90 => 21,  78 => 32,  72 => 21,  62 => 19,  53 => 24,  50 => 14,  48 => 22,  41 => 9,  39 => 8,  35 => 8,  30 => 6,  27 => 6,  354 => 163,  345 => 160,  341 => 159,  338 => 158,  333 => 157,  331 => 141,  323 => 138,  321 => 149,  314 => 145,  307 => 141,  300 => 137,  293 => 121,  286 => 129,  279 => 125,  272 => 121,  257 => 109,  250 => 138,  243 => 96,  236 => 97,  226 => 87,  223 => 88,  215 => 83,  212 => 82,  209 => 81,  204 => 78,  201 => 77,  196 => 69,  190 => 72,  182 => 88,  180 => 64,  170 => 64,  163 => 54,  156 => 56,  152 => 48,  149 => 47,  146 => 74,  138 => 42,  133 => 47,  129 => 42,  126 => 45,  123 => 44,  117 => 57,  114 => 31,  111 => 40,  108 => 42,  102 => 35,  98 => 24,  91 => 42,  87 => 29,  84 => 29,  81 => 28,  73 => 23,  70 => 22,  67 => 20,  64 => 20,  58 => 15,  52 => 12,  49 => 13,  46 => 15,  40 => 7,  36 => 7,  33 => 6,  31 => 4,  28 => 3,);
    }
}
